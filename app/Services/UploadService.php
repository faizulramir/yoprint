<?php

namespace App\Services;

use App\Http\Resources\UploadResource;
use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadService
{

    public function getUploads()
    {
        return UploadResource::collection(Upload::all());
    }

    public function storeUpload(Request $request)
    {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = \Str::random(20) . '.' . $extension;
        $file->storeAs('uploads', $filename, 'public');

        $upload = Upload::create([
            'filename' => $filename,
            'status' => 'pending',
        ]);

        ProcessCsvUpload::dispatch($upload);
    }
}