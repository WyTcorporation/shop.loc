<?php

use App\Models\Product;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
use Laravel\Scout\Jobs\RemoveFromSearch;

it('indexes on create/update and removes on delete', function () {
    // Увімкнути режим черги для Scout у тесті
    config()->set('scout.queue', true);

    // Фейкаємо чергу, щоб перехопити Scout-джоби
    Queue::fake();

    // created ⇒ MakeSearchable
    $p = Product::factory()->create(['is_active' => true]);
    Queue::assertPushed(MakeSearchable::class, function ($job) use ($p) {
        return $job->models->first()->is($p);
    });

    // updated (active) ⇒ знову MakeSearchable
    $p->update(['name' => 'Renamed']);
    Queue::assertPushed(MakeSearchable::class, function ($job) use ($p) {
        return $job->models->first()->is($p);
    });

    // updated (deactivate) ⇒ RemoveFromSearch
    $p->update(['is_active' => false]);
    Queue::assertPushed(RemoveFromSearch::class, function ($job) use ($p) {
        return $job->models->first()->is($p);
    });

    // deleted ⇒ RemoveFromSearch
    $p->delete();
    Queue::assertPushed(RemoveFromSearch::class, function ($job) use ($p) {
        return $job->models->first()->is($p);
    });
});
