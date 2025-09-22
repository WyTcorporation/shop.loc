<?php

namespace App\Filament\Mine\Resources\PushCampaigns;

use App\Filament\Mine\Resources\PushCampaigns\Pages\CreatePushCampaign;
use App\Filament\Mine\Resources\PushCampaigns\Pages\EditPushCampaign;
use App\Filament\Mine\Resources\PushCampaigns\Pages\ListPushCampaigns;
use App\Filament\Mine\Resources\PushCampaigns\Schemas\PushCampaignForm;
use App\Filament\Mine\Resources\PushCampaigns\Tables\PushCampaignsTable;
use App\Models\MarketingCampaign;
use App\Services\Marketing\CampaignScheduleService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class PushCampaignResource extends Resource
{
    protected static ?string $model = MarketingCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return PushCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PushCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPushCampaigns::route('/'),
            'create' => CreatePushCampaign::route('/create'),
            'edit' => EditPushCampaign::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.push_campaigns.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.push_campaigns.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.marketing');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', static::getModel()), 403);

        return parent::getEloquentQuery()
            ->push()
            ->with('template')
            ->withCount('segments');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::user()?->can('viewAny', static::getModel())) {
            return null;
        }

        return (string) MarketingCampaign::query()->push()->count();
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
