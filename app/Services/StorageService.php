<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    // ------------------------ Save Image File
    public static function saveImage($imgFile, $directory, $prefix = null)
    {
        // Sanitize file name
        $img_name = $prefix . uniqid() . '.' . $imgFile->getClientOriginalExtension();
        // Store the file
        $imgFile->storeAs('public/images/' . $directory, $img_name);

        return $img_name;
    }

    // ------------------------ Delete Image File
    public static function deleteImage($path)
    {
        Storage::delete('public/images/' . $path);
    }

    //--------------------------- Save File Image & video
    public static function saveFile($file, $directory, $prefix = "")
    {
        $file_name = $prefix . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store the file
        $file->storeAs('public/media/' . $directory, $file_name);
        return $file_name;
    }

    // ------------------------ Delete File
    public static function deleteFile($path)
    {
        Storage::delete('public/media/' . $path);
    }
}
