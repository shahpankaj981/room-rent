<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Support\Facades\Storage;
use App\Services\FileManager;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /** returns the image file
     * @param $filename
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFile($filename)
    {
        $entry = Image::whereFilename($filename)->first();
        if (!$entry) {
            return response("invalid filename");
        }
        try {
            $file = Storage::disk('local')->get($entry->filename);
        } catch (Exception $e) {
            return response("file can't be read");
        }

        return response($file, 200)->header('Content-Type', $entry->mime);
    }
}
