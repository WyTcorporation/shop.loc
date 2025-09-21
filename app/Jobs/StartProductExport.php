<?php

namespace App\Jobs;

use App\Models\ProductExport;
use App\Services\Products\ProductExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartProductExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected int $exportId,
    ) {
    }

    public function handle(ProductExportService $service): void
    {
        $export = ProductExport::query()->find($this->exportId);

        if (! $export) {
            return;
        }

        $service->start($export);
    }
}
