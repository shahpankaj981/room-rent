<?php

namespace App\Http\Controllers;

use App\Fileentry;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileentryController extends Controller
{
    public function __constructor()
    {
        //
    }

    public function save($file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_random(20) . $file->getFilename() . '.' . $extension;

        try {
            Storage::disk('local')->put($filename, File::get($file));
        } catch (Exception $e) {
            return null;
        }

        $filedata = [
            'mime' => $file->getClientMimeType(),
            'original_filename' => $file->getClientOriginalName(),
            'filename' => $filename
        ];
        $entry = Fileentry::create($filedata);
        return $entry;
    }

    public function get($filename)
    {
        $entry = Fileentry::whereFilename($filename)->first();
        if(!$entry){
            return response("invalid filename");
        }
        try{
            $file = Storage::disk('local')->get($entry->filename);
        }
        catch (Exception $e) {
            return response("file can't be read");
        }

        return response($file, 200) ->header('Content-Type', $entry->mime);
    }
}
