<?php

namespace App\Filament\Mine\Resources\Roles\Tables;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as SpatieRole;

class RolesTable
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
                TextColumn::make('permissions')
                    ->label(__('shop.users.fields.permissions'))
                    ->formatStateUsing(fn (SpatieRole $record): string => $record->permissions
                        ->pluck('name')
                        ->sort()
                        ->take(3)
                        ->join(', '))
                    ->tooltip(fn (SpatieRole $record): ?string => $record->permissions->isEmpty()
                        ? null
                        : $record->permissions->pluck('name')->sort()->join(', '))
                    ->toggleable(),
                TextColumn::make('users')
                    ->label(__('shop.admin.resources.users.plural_label'))
                    ->formatStateUsing(fn (SpatieRole $record): string => $record->users
                        ->pluck('name')
                        ->sort()
                        ->take(3)
                        ->join(', '))
                    ->tooltip(fn (SpatieRole $record): ?string => $record->users->isEmpty()
                        ? null
                        : $record->users->pluck('name')->sort()->join(', '))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (SpatieRole $record): bool => Auth::user()?->can('update', $record) ?? false),
                DeleteAction::make()
                    ->visible(fn (SpatieRole $record): bool => Auth::user()?->can('delete', $record) ?? false),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('syncUsers')
                    ->label(__('shop.admin.resources.roles.bulk_actions.sync_users.label'))
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('users')
                            ->label(__('shop.admin.resources.roles.bulk_actions.sync_users.users_field'))
                            ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        Toggle::make('replace')
                            ->label(__('shop.admin.resources.roles.bulk_actions.sync_users.replace_toggle'))
                            ->default(false),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $userIds = $data['users'] ?? [];

                        if (empty($userIds)) {
                            return;
                        }

                        $roleNames = $records->pluck('name')->all();

                        User::query()
                            ->whereIn('id', $userIds)
                            ->get()
                            ->each(function (User $user) use ($roleNames, $data): void {
                                if (($data['replace'] ?? false) === true) {
                                    $user->syncRoles($roleNames);

                                    return;
                                }

                                $user->syncRoles(array_unique([
                                    ...$user->getRoleNames()->all(),
                                    ...$roleNames,
                                ]));
                            });
                    })
                    ->authorize(fn (): bool => Auth::user()?->can(PermissionEnum::ManageUsers->value) ?? false),
            ]);
    }
}
