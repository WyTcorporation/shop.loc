<?php

namespace App\Jobs;

use App\Models\ProductImport;
use App\Services\Products\ProductImportService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartProductImport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected int $importId,
    ) {
    }

    public function handle(ProductImportService $service): void
    {
        $import = ProductImport::query()->find($this->importId);

        if (! $import) {
            return;
        }

        $service->start($import);
    }
}
