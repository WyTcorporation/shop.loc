<?php

namespace App\Filament\Mine\Resources\PushCampaigns\Schemas;

use App\Models\CampaignTemplate;
use App\Models\MarketingCampaign;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PushCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        $timezoneOptions = collect([
            'UTC' => 'UTC',
            config('app.timezone') => config('app.timezone'),
        ])->filter()->unique();

        return $schema->components([
            Section::make(__('Campaign details'))
                ->schema([
                    Hidden::make('type')
                        ->default(MarketingCampaign::TYPE_PUSH)
                        ->dehydrateStateUsing(fn () => MarketingCampaign::TYPE_PUSH),
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required(),
                    Select::make('template_id')
                        ->label(__('Template'))
                        ->options(fn () => CampaignTemplate::query()
                            ->where('channel', MarketingCampaign::TYPE_PUSH)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),
                    Select::make('status')
                        ->label(__('Status'))
                        ->options([
                            'draft' => __('Draft'),
                            'scheduled' => __('Scheduled'),
                            'running' => __('Running'),
                            'completed' => __('Completed'),
                        ])
                        ->default('draft'),
                    DateTimePicker::make('scheduled_for')
                        ->label(__('Scheduled for')),
                    Select::make('segments')
                        ->relationship(
                            'segments',
                            'name',
                            fn ($query) => $query->select(['customer_segments.id', 'customer_segments.name'])
                        )
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->label(__('Segments'))
                        ->columnSpanFull()
                        ->native(false),
                    KeyValue::make('settings')
                        ->label(__('Settings'))
                        ->columnSpanFull(),
                    KeyValue::make('audience_filters')
                        ->label(__('Audience filters'))
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make(__('Metrics'))
                ->schema([
                    TextInput::make('open_count')
                        ->label(__('Impressions'))
                        ->disabled(),
                    TextInput::make('click_count')
                        ->label(__('Clicks'))
                        ->disabled(),
                    TextInput::make('conversion_count')
                        ->label(__('Conversions'))
                        ->disabled(),
                ])
                ->columns(3),
            Section::make(__('Schedule'))
                ->schema([
                    TextInput::make('schedule_cron_expression')
                        ->label(__('Cron expression'))
                        ->helperText(__('Define the CRON expression used for recurring sends.'))
                        ->default('*/30 * * * *')
                        ->dehydrated(false),
                    Select::make('schedule_timezone')
                        ->label(__('Timezone'))
                        ->options($timezoneOptions->toArray())
                        ->default(config('app.timezone', 'UTC'))
                        ->dehydrated(false)
                        ->native(false),
                    DateTimePicker::make('schedule_starts_at')
                        ->label(__('Starts at'))
                        ->dehydrated(false),
                    DateTimePicker::make('schedule_ends_at')
                        ->label(__('Ends at'))
                        ->dehydrated(false),
                ])
                ->columns(2),
        ]);
    }
}
