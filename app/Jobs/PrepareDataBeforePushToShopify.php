<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use function Pest\Laravel\json;

class PrepareDataBeforePushToShopify implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $fileContactAsCollection = Excel::toCollection(new ProductsImport, $this->filePath)->first();
        $collectedContent = collect($fileContactAsCollection)->groupBy('handle')->chunk(2)->toArray();
        foreach ($collectedContent as $collected) {
            SendProductToShopify::dispatch($collected);
        }

    }
}
