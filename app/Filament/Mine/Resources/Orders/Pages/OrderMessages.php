<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Message;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class OrderMessages extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.mine.resources.orders.pages.messages';

    public array $data = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $messages = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorize('view', $this->record);

        $this->loadMessages();

        $this->form->fill(['body' => '']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('body')
                    ->label('Message')
                    ->rows(4)
                    ->required()
                    ->maxLength(2000),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $this->authorize('createMessage', $this->record);

        $data = $this->form->getState();

        $this->record->messages()->create([
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        $this->form->fill(['body' => '']);
        $this->loadMessages();

        Notification::make()
            ->title(__('Message sent'))
            ->success()
            ->send();
    }

    protected function loadMessages(): void
    {
        $currentUserId = Auth::id();

        $this->messages = $this->record->messages()
            ->with('user:id,name')
            ->oldest('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'user' => $message->user,
                'is_author' => $message->user_id === $currentUserId,
            ])
            ->all();
    }

    protected function getViewData(): array
    {
        return [
            'order' => $this->record,
        ];
    }
}
