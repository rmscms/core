<?php

namespace RMS\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileServeController extends BaseController
{
    // Admin-only file serve for private attachments
    public function show(Request $request, int $id)
    {
        $row = DB::table('attachments')->where('id', $id)->first();
        if (!$row) {
            abort(404);
        }

        $path = $row->path;
        $access = $row->access_scope ?? 'private';
        $disk = $access === 'public' ? 'public' : 'local';

        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return new StreamedResponse(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
