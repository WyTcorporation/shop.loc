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
                    ->label(__('shop.reviews.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('user_id')
                    ->label(__('shop.reviews.fields.user'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('rating')
                    ->label(__('shop.reviews.fields.rating'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label(__('shop.reviews.fields.status'))
                    ->options([
                        Review::STATUS_PENDING => __('shop.reviews.statuses.pending'),
                        Review::STATUS_APPROVED => __('shop.reviews.statuses.approved'),
                        Review::STATUS_REJECTED => __('shop.reviews.statuses.rejected'),
                    ])
                    ->required(),

                Textarea::make('text')
                    ->label(__('shop.reviews.fields.text'))
                    ->rows(6)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
