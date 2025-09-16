<?php

namespace App\Filament\Mine\Resources\Reviews;

use App\Filament\Mine\Resources\Reviews\Pages\EditReview;
use App\Filament\Mine\Resources\Reviews\Pages\ListReviews;
use App\Filament\Mine\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Mine\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|null|\UnitEnum $navigationGroup = 'Content';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Review::query()
            ->where('status', Review::STATUS_PENDING)
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }
}
