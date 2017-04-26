<?php

namespace App\Services;

use App\Image;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Illuminate\Support\Facades\File;
use App\Post;
use App\User;

/**
 * Class FileManager
 * @package App\Services
 */
class FileManager
{

    protected $image;
    protected $storage;
    protected $object;

    /**
     * FileManager constructor.
     * @param image   $image
     * @param Storage $storage
     */
    public function __construct(Image $image, Storage $storage)
    {
        $this->image   = $image;
        $this->storage = $storage;
    }

    /**
     * saves file/s in the database
     * @param $object
     * is the object type which is to be created which is to be associated with image
     * @param $files
     * is the list of files that are passed
     * @param $model
     * can be user or post
     * @return null|string
     */
    public function saveFile($object, $files, $model)
    {
        if ($model = "user") {
            $this->object = new User;
            $this->object = $object;
        } elseif ($model = "post") {
            $this->object = new Post();
            $this->object = $object;
        }
        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename  = str_random(20).$file->getFilename().'.'.$extension;;
            try {
                Storage::disk('local')->put($filename, File::get($file));
            } catch (Exception $e) {
                return null;
            }
            $image                    = new Image();
            $image->mime              = $file->getClientMimeType();
            $image->original_filename = $file->getClientOriginalName();
            $image->filename          = $filename;
            $this->object->image()->save($image);
        }

        return $this->object;
    }


}