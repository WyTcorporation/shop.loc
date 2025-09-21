<?php

namespace App\Filament\Mine\Resources\Tests\Schemas;

use App\Models\CampaignTemplate;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Test details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required(),
                    Select::make('campaign_id')
                        ->label(__('Campaign'))
                        ->relationship('campaign', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),
                    Select::make('variant_a_template_id')
                        ->label(__('Variant A template'))
                        ->options(fn () => CampaignTemplate::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),
                    Select::make('variant_b_template_id')
                        ->label(__('Variant B template'))
                        ->options(fn () => CampaignTemplate::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),
                    Select::make('winning_template_id')
                        ->label(__('Winning template'))
                        ->options(fn () => CampaignTemplate::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('status')
                        ->label(__('Status'))
                        ->options([
                            'draft' => __('Draft'),
                            'running' => __('Running'),
                            'completed' => __('Completed'),
                        ])
                        ->default('draft'),
                    TextInput::make('traffic_split_a')
                        ->label(__('Variant A traffic %'))
                        ->numeric()
                        ->default(50),
                    TextInput::make('traffic_split_b')
                        ->label(__('Variant B traffic %'))
                        ->numeric()
                        ->default(50),
                    KeyValue::make('metrics')
                        ->label(__('Metrics'))
                        ->columnSpanFull()
                        ->helperText(__('Track aggregated opens, clicks and conversions for this test.')),
                ])
                ->columns(2),
        ]);
    }
}
