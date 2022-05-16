<?php

namespace App\Http\Controllers\image;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller {
    public static function storeImage($folder, $file){
        if($file){
            $filepath = env('FILESPATH');
            $path = Storage::putFile($folder, $file);

            if($path){
                return $filepath.$path;
            } else {
                return '';
            }
            
        } else {
            return '';
        }
    }

    public static function updateImage($folder, $url, $newFile){
        if($url){
            $deleted = self::deleteImage($folder, $url);
            if($deleted){
                return self::storeImage($folder, $newFile);
            }
        } else {
            return self::storeImage($folder, $newFile);
        }
    }

    public static function deleteImage($folder, $url){
        $filename = explode("$folder/", $url)[1];
        $fileExists = Storage::exists("$folder/$filename");

        if($fileExists){
            Storage::delete("$folder/$filename");
            return true;
        }

        return false;
    }
}
