<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\UploadStatusUpdated;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function handle()
    {
        $upload = Upload::find($this->uploadId);
        if (!$upload)
            return;

        $upload->status = 'processing';
        $upload->save();
        event(new UploadStatusUpdated($upload->id, 'processing'));

        try {
            $path = storage_path("app/public/uploads/{$upload->filename}");
            $file = fopen($path, 'r');

            $header = fgetcsv($file);
            if (!$header)
                throw new \Exception('CSV header is missing or invalid.');

            $newItems = [];
            $batchSize = 250;
            $existingKeys = Item::pluck('unique_key')->toArray();

            while (($row = fgetcsv($file)) !== false) {
                if (empty($row[0]))
                    continue;

                $mappedRow = array_combine($header, $row);

                // Clean each value
                $mappedRow = array_map(function ($value) {
                    $clean = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    return preg_replace('/[^\PC\s]/u', '', $clean);
                }, $mappedRow);

                $uniqueKey = $mappedRow['UNIQUE_KEY'] ?? null;

                if (!$uniqueKey)
                    continue;

                // Prepare row data
                $data = [
                    'unique_key' => $uniqueKey,
                    'product_title' => $mappedRow['PRODUCT_TITLE'] ?? null,
                    'product_description' => $mappedRow['PRODUCT_DESCRIPTION'] ?? null,
                    'style' => $mappedRow['STYLE#'] ?? null,
                    'sanmar_mainframe_color' => $mappedRow['SANMAR_MAINFRAME_COLOR'] ?? null,
                    'size' => $mappedRow['SIZE'] ?? null,
                    'color_name' => $mappedRow['COLOR_NAME'] ?? null,
                    'piece_price' => $mappedRow['PIECE_PRICE'] ?? null,
                    'file_id' => $upload->id,
                    'updated_at' => now(),
                ];

                if (in_array($uniqueKey, $existingKeys)) {
                    // Check if values have changed
                    $existing = Item::where('unique_key', $uniqueKey)->first();
                    $hasChanges = false;
                    foreach ($data as $key => $value) {
                        if ($key !== 'file_id' && $existing->$key !== $value) {
                            $hasChanges = true;
                            break;
                        }
                    }

                    if ($hasChanges) {
                        $existing->fill($data);
                        $existing->file_id = $upload->id;
                        $existing->save();
                    }

                } else {
                    $data['created_at'] = now();
                    $newItems[] = $data;

                    if (count($newItems) >= $batchSize) {
                        Item::upsert($newItems, ['unique_key']);
                        $newItems = [];
                    }
                }
            }

            if (!empty($newItems)) {
                Item::upsert($newItems, ['unique_key']);
            }

            fclose($file);

            $upload->status = 'completed';
            event(new UploadStatusUpdated($upload->id, 'completed'));
        } catch (\Exception $e) {
            $this->fail($e);
            $upload->status = 'failed';
            event(new UploadStatusUpdated($upload->id, 'failed'));
            \Log::error($e->getMessage());
        }

        $upload->save();
    }
}
