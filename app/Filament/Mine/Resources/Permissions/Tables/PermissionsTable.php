<?php

namespace App\Filament\Mine\Resources\Permissions\Tables;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('shop.common.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles')
                    ->label(__('shop.users.fields.roles'))
                    ->formatStateUsing(fn (SpatiePermission $record): string => $record->roles
                        ->pluck('name')
                        ->sort()
                        ->take(3)
                        ->join(', '))
                    ->tooltip(fn (SpatiePermission $record): ?string => $record->roles->isEmpty()
                        ? null
                        : $record->roles->pluck('name')->sort()->join(', '))
                    ->toggleable(),
                TextColumn::make('users')
                    ->label(__('shop.admin.resources.users.plural_label'))
                    ->formatStateUsing(fn (SpatiePermission $record): string => $record->users
                        ->pluck('name')
                        ->sort()
                        ->take(3)
                        ->join(', '))
                    ->tooltip(fn (SpatiePermission $record): ?string => $record->users->isEmpty()
                        ? null
                        : $record->users->pluck('name')->sort()->join(', '))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (SpatiePermission $record): bool => Auth::user()?->can('update', $record) ?? false),
                DeleteAction::make()
                    ->visible(fn (SpatiePermission $record): bool => Auth::user()?->can('delete', $record) ?? false),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('syncUsers')
                    ->label(__('shop.admin.resources.permissions.bulk_actions.sync_users.label'))
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('users')
                            ->label(__('shop.admin.resources.permissions.bulk_actions.sync_users.users_field'))
                            ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        Toggle::make('replace')
                            ->label(__('shop.admin.resources.permissions.bulk_actions.sync_users.replace_toggle'))
                            ->default(false),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $userIds = $data['users'] ?? [];

                        if (empty($userIds)) {
                            return;
                        }

                        $permissionNames = $records->pluck('name')->all();

                        User::query()
                            ->whereIn('id', $userIds)
                            ->get()
                            ->each(function (User $user) use ($permissionNames, $data): void {
                                if (($data['replace'] ?? false) === true) {
                                    $user->syncPermissions($permissionNames);

                                    return;
                                }

                                $user->syncPermissions(array_unique([
                                    ...$user->getPermissionNames()->all(),
                                    ...$permissionNames,
                                ]));
                            });
                    })
                    ->authorize(fn (): bool => Auth::user()?->can(PermissionEnum::ManageUsers->value) ?? false),
            ]);
    }
}
