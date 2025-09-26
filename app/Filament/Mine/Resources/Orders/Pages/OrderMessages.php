<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Message;
use App\Services\Orders\OrderMessageService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
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

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Textarea::make('body')
                    ->label(__('shop.conversation.message'))
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

        $userId = Auth::id();

        if ($userId === null) {
            abort(401);
        }

        app(OrderMessageService::class)->create($this->record, $userId, $data['body']);

        $this->form->fill(['body' => '']);
        $this->loadMessages();

        Notification::make()
            ->title(__('shop.conversation.sent'))
            ->success()
            ->send();
    }

    protected function loadMessages(): void
    {
        $currentUserId = Auth::id();

        if ($currentUserId !== null) {
            app(OrderMessageService::class)->markAsRead($this->record, $currentUserId);
        }

        $this->messages = $this->record->messages()
            ->with('user:id,name')
            ->oldest('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'read_at' => $message->read_at,
                'user' => $message->user,
                'is_author' => $message->user_id === $currentUserId,
                'is_read' => $message->isRead(),
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
