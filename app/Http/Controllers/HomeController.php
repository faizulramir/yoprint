<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\UploadService;

class HomeController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    public function index()
    {
        $uploads = $this->uploadService->getUploads();

        return Inertia::render('welcome', [
            'uploads' => $uploads
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:50000',
        ]);

        $this->uploadService->storeUpload($request);

        return redirect()->route('home');
    }
}
