<?php

namespace App\Filament\Mine\Resources\Tests;

use App\Filament\Mine\Resources\Tests\Pages\CreateTest;
use App\Filament\Mine\Resources\Tests\Pages\EditTest;
use App\Filament\Mine\Resources\Tests\Pages\ListTests;
use App\Filament\Mine\Resources\Tests\Schemas\TestForm;
use App\Filament\Mine\Resources\Tests\Tables\TestsTable;
use App\Models\CampaignTest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TestResource extends Resource
{
    protected static ?string $model = CampaignTest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return TestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTests::route('/'),
            'create' => CreateTest::route('/create'),
            'edit' => EditTest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) CampaignTest::count();
    }

    public static function getModelLabel(): string
    {
        return __('A/B test');
    }

    public static function getPluralModelLabel(): string
    {
        return __('A/B tests');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('campaign');
    }
}
