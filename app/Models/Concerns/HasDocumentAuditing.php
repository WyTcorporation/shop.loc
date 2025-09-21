<?php

namespace App\Models\Concerns;

use App\Models\DocumentAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait HasDocumentAuditing
{
    public static function bootHasDocumentAuditing(): void
    {
        static::created(function (Model $model): void {
            $model->recordDocumentAudit('created');
        });

        static::updated(function (Model $model): void {
            $model->recordDocumentAudit('updated');
        });

        static::deleted(function (Model $model): void {
            $model->recordDocumentAudit('deleted');
        });
    }

    protected function recordDocumentAudit(string $event): void
    {
        $user = Auth::user();
        $changes = $this->resolveChangesForEvent($event);

        DocumentAudit::create([
            'document_type' => $this->getMorphClass(),
            'document_id' => $this->getKey(),
            'order_id' => method_exists($this, 'getOrderIdForAudit')
                ? $this->getOrderIdForAudit()
                : $this->getAttribute('order_id'),
            'user_id' => $user?->getAuthIdentifier(),
            'event' => $event,
            'changes' => $changes,
        ]);
    }

    protected function resolveChangesForEvent(string $event): ?array
    {
        return match ($event) {
            'created' => $this->attributesToArray(),
            'updated' => $this->extractUpdatedChanges(),
            'deleted' => $this->getOriginal(),
            default => null,
        };
    }

    protected function extractUpdatedChanges(): ?array
    {
        $dirty = array_keys($this->getDirty());

        if ($dirty === []) {
            return null;
        }

        $before = Arr::only($this->getOriginal(), $dirty);
        $after = Arr::only($this->getAttributes(), $dirty);

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    protected function getOrderIdForAudit(): ?int
    {
        return $this->getAttribute('order_id');
    }
}
