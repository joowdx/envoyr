<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download an electronic document
     */
    public function __invoke(Document $document): StreamedResponse
    {
        // Check if document is electronic and has attachments
        if (!$document->electronic || !$document->attachment || $document->attachment->files->isEmpty()) {
            abort(404, 'Electronic document file not found.');
        }

        // Check if user has access to this document (basic check)
        $transmittal = $document->transmittals()
            ->where('to_office_id', Auth::user()->office_id)
            ->whereNotNull('received_at')
            ->first();

        if (!$transmittal) {
            abort(403, 'You do not have permission to download this document.');
        }

        $attachment = $document->attachment;
        $firstFile = $attachment->files->first();
        $fileName = $attachment->paths->first();

        // Check if file exists in storage
        if (!Storage::exists($firstFile)) {
            abort(404, 'Document file not found in storage.');
        }

        // Return the file as a download
        return Storage::download($firstFile, $fileName);
    }
}