<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Filament\Mine\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // На всяк випадок — після збереження самої форми теж перерахуємо total
    protected function afterSave(): void
    {
        $this->record->recalculateTotal();
        $this->dispatch('order-items-updated');
    }

    #[On('order-items-updated')]
    public function refreshTotals(): void
    {
        // Підтягнути свіжі дані моделі
        $this->record->refresh();

        // Перезаповнити форму свіжим total
        // Варіант A: заповнити тільки одне поле
        $this->form->fill([
            'total' => $this->record->total,
        ]);

        // Варіант B (більш «грубо», але просто): повністю перезібрати форму
        // $this->fillForm(); // якщо цей метод доступний у вашій версії (у v4 зазвичай є)
    }
}
