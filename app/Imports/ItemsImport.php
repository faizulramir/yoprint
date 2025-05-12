<?php

namespace App\Imports;

use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ItemsImport implements ToModel, WithHeadingRow, WithChunkReading, WithUpserts
{
    protected $uploadId;
    protected $batchSize = 2000;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function chunkSize(): int
    {
        return $this->batchSize;
    }

    public function model(array $row)
    {
        $uniqueKey = $row['unique_key'] ?? null;

        if (!$uniqueKey) {
            return null;
        }

        $data = [
            'unique_key' => $uniqueKey,
            'product_title' => $this->clean($row['product_title'] ?? null),
            'product_description' => $this->clean($row['product_description'] ?? null),
            'style' => $this->clean($row['style#'] ?? null),
            'sanmar_mainframe_color' => $this->clean($row['sanmar_mainframe_color'] ?? null),
            'size' => $this->clean($row['size'] ?? null),
            'color_name' => $this->clean($row['color_name'] ?? null),
            'piece_price' => $this->clean($row['piece_price'] ?? null),
            'file_id' => $this->uploadId,
            'updated_at' => now(),
        ];

        return new Item($data);
    }

    protected function clean($value)
    {
        $clean = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_replace('/[^\PC\s]/u', '', $clean);
    }

    public function upsertColumns(): array
    {
        return ['unique_key'];
    }

    public function uniqueBy(): string
    {
        return 'unique_key';
    }
}
