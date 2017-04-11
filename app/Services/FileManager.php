<?php

namespace App\Services;

use App\Fileentry;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

/**
 * Class FileManager
 * @package App\Services
 */
class FileManager
{

    protected $fileEntry;
    protected $storage;

    /**
     * FileManager constructor.
     * @param FileEntry $fileEntry
     * @param Storage   $storage
     */
    public function __construct(Fileentry $fileEntry, Storage $storage)
    {
        $this->fileEntry = $fileEntry;
        $this->storage   = $storage;
    }

    /**
     * saves file in the database
     * @param $file
     * @return null|string
     */
    public function saveFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = str_random(20).$file->getFilename().'.'.$extension;

        try {
            Storage::disk('local')->put($filename, File::get($file));
        } catch (Exception $e) {
            return null;
        }
        $fileData = [
            'mime'              => $file->getClientMimeType(),
            'original_filename' => $file->getClientOriginalName(),
            'filename'          => $filename,
        ];
        try {
            $entry = $this->fileEntry->create($fileData);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $entry;
    }

    /** returns the image file
     * @param $filename
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFile($filename)
    {
        $entry = Fileentry::whereFilename($filename)->first();
        if (!$entry) {
            return response("invalid filename");
        }
        try {
            $file = $this->storage->disk('local')->get($entry->filename);
        } catch (Exception $e) {
            return response("file can't be read");
        }

        return response($file, 200)->header('Content-Type', $entry->mime);
    }
}