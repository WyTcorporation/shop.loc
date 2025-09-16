<?php

namespace App\Filament\Mine\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        Review::STATUS_PENDING => 'Pending',
                        Review::STATUS_APPROVED => 'Approved',
                        Review::STATUS_REJECTED => 'Rejected',
                    ])
                    ->required(),

                Textarea::make('text')
                    ->label('Review text')
                    ->rows(6)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
