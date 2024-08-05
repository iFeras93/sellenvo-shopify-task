<?php

namespace App\Imports;

use App\Jobs\PushProductToShopify;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rows[] = $row->toArray();

            if (count($this->rows) == 5) {
                PushProductToShopify::dispatch($this->rows);
                $this->rows = [];
            }
        }
        if (count($this->rows) > 0) {
            PushProductToShopify::dispatch($this->rows);
        }
    }

    public function chunkSize(): int
    {
        return 2;
    }
}
