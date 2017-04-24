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
     * saves file in the database
     * @param $file
     * @return null|string
     */
    public function saveFile($object, $files, $model)
    {
//        dd($files);
        if ($model = "user") {
            $this->object = new User;
            $this->object = $object;
        } elseif ($model = "post") {
            $this->object = new Post();
            $this->object = $object;
        }
//        dd($this->object);
        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename  = str_random(20).$file->getFilename().'.'.$extension;;
            try {
                Storage::disk('local')->put($filename, File::get($file));
            } catch (Exception $e) {
                return null;
            }
//            $fileData = [
//                'mime'              => $file->getClientMimeType(),
//                'original_filename' => $file->getClientOriginalName(),
//                'filename'          => $filename,
//            ];
            $image                    = new Image();
            $image->mime              = $file->getClientMimeType(); //
            $image->original_filename = $file->getClientOriginalName(); //
            $image->filename          = $filename; //
            $this->object->image()->save($image);//
//            dd($this->image);
//            try {
////                $entry = $this->image->create($fileData);
//                $this->object->image()->save($this->image);//
//            } catch (Exception $e) {
//                return $e->getMessage();
//            }

        }

        return $this->object;
    }


}