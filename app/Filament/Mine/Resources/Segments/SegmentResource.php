<?php

namespace App\Filament\Mine\Resources\Segments;

use App\Filament\Mine\Resources\Segments\Pages\CreateSegment;
use App\Filament\Mine\Resources\Segments\Pages\EditSegment;
use App\Filament\Mine\Resources\Segments\Pages\ListSegments;
use App\Filament\Mine\Resources\Segments\Schemas\SegmentForm;
use App\Filament\Mine\Resources\Segments\Tables\SegmentsTable;
use App\Models\CustomerSegment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SegmentResource extends Resource
{
    protected static ?string $model = CustomerSegment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return SegmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SegmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegments::route('/'),
            'create' => CreateSegment::route('/create'),
            'edit' => EditSegment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) CustomerSegment::count();
    }

    public static function getModelLabel(): string
    {
        return __('Segment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Segments');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('campaigns');
    }
}
