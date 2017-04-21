<?php

namespace App\Services;

use App\FileEntry;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Illuminate\Support\Facades\File;

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
    public function __construct(FileEntry $fileEntry, Storage $storage)
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


}