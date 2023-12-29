<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Storage;

class FileRemover
{
    public static function remove($filePath,$diskType)
    {
        return Storage::disk($diskType)->delete($filePath);
    }

    public static function removeMany($filesPath,$diskType)
    {
        foreach($filesPath as $filePath)
            Storage::disk($diskType)->delete($filePath);
    }
}