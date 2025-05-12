<?php

namespace App\Jobs;

use App\Events\UploadStatusUpdated;
use App\Imports\ItemsImport;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Upload $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function handle()
    {
        $upload = $this->upload;

        $upload->status = 'processing';
        $upload->save();
        event(new UploadStatusUpdated($upload->id, 'processing'));

        try {
            $filePath = Storage::disk('public')->path("uploads/{$upload->filename}");
            Excel::import(new ItemsImport($upload->id), $filePath);

            $upload->status = 'completed';
            event(new UploadStatusUpdated($upload->id, 'completed'));
        } catch (\Exception $e) {
            $this->fail($e);
            $upload->status = 'failed';
            event(new UploadStatusUpdated($upload->id, 'failed'));
            Log::error($e->getMessage());
        }

        $upload->save();
    }
}
