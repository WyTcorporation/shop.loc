<?php

namespace App\Filament\Mine\Resources\Vendors\Tables;

use App\Models\Vendor;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = Vendor::query()->with('user');

                if ($vendor = Auth::user()?->vendor) {
                    $query->whereKey($vendor->id);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label(__('shop.common.owner'))
                    ->sortable(),
                TextColumn::make('contact_email')
                    ->label(__('shop.common.email'))
                    ->toggleable(),
                TextColumn::make('contact_phone')
                    ->label(__('shop.common.phone'))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn () => ! Auth::user()?->vendor),
            ])
            ->defaultSort('name');
    }
}
