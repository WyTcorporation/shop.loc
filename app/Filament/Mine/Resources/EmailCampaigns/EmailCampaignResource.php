<?php

namespace App\Filament\Mine\Resources\EmailCampaigns;

use App\Filament\Mine\Resources\EmailCampaigns\Pages\CreateEmailCampaign;
use App\Filament\Mine\Resources\EmailCampaigns\Pages\EditEmailCampaign;
use App\Filament\Mine\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Mine\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Mine\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Models\MarketingCampaign;
use App\Services\Marketing\CampaignScheduleService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = MarketingCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return EmailCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) MarketingCampaign::email()->count();
    }

    public static function getModelLabel(): string
    {
        return __('Email campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Email campaigns');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->email()
            ->with('template')
            ->withCount('segments');
    }

    public static function persistSchedule(MarketingCampaign $campaign, array $data): void
    {
        $cron = trim((string) ($data['cron_expression'] ?? ''));
        $timezone = $data['timezone'] ?? config('app.timezone', 'UTC');
        $startsAt = $data['starts_at'] ?? null;
        $endsAt = $data['ends_at'] ?? null;

        if ($cron === '') {
            if ($campaign->schedule()->exists()) {
                $campaign->schedule()->delete();
            }

            return;
        }

        $schedule = $campaign->schedule()->firstOrNew();
        $schedule->cron_expression = $cron;
        $schedule->timezone = $timezone ?: 'UTC';
        $schedule->starts_at = $startsAt ? Date::parse($startsAt) : null;
        $schedule->ends_at = $endsAt ? Date::parse($endsAt) : null;
        $schedule->save();

        app(CampaignScheduleService::class)->ensureNextRun($campaign->fresh('schedule'));
    }
}
