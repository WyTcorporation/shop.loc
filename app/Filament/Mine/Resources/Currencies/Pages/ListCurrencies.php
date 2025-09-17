<?php

namespace App\Filament\Mine\Resources\Currencies\Pages;

use App\Filament\Mine\Resources\Currencies\CurrencyResource;
use App\Services\Currency\CurrencyConverter;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ListCurrencies extends ListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Actions\Action::make('refreshRates')
                ->label('Оновити курси')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        $exitCode = Artisan::call('currency:update');
                        $output = trim(Artisan::output());

                        if ($exitCode === 0) {
                            Notification::make()
                                ->title('Курси валют оновлено')
                                ->body($output !== '' ? $output : null)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Не вдалося оновити курси валют')
                                ->body($output !== '' ? $output : null)
                                ->danger()
                                ->send();
                        }
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Не вдалося оновити курси валют')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }

                    app(CurrencyConverter::class)->refreshRates();
                }),
        ];
    }
}
