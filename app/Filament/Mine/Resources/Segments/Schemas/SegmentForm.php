<?php

namespace App\Filament\Mine\Resources\Segments\Schemas;

use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Segment details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required(),
                    Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true),
                    Select::make('campaigns')
                        ->label(__('Campaigns'))
                        ->relationship(
                            name: 'campaigns',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->select(
                                'marketing_campaigns.id',
                                'marketing_campaigns.name',
                            ),
                        )
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull()
                        ->native(false),
                    KeyValue::make('conditions')
                        ->label(__('Conditions'))
                        ->columnSpanFull()
                        ->helperText(__('Define attribute/value pairs used to build the customer audience.')),
                ])
                ->columns(2),
        ]);
    }
}
