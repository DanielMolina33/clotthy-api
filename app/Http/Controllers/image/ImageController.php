<?php

namespace App\Http\Controllers\image;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller {
    public static function storeImage($file){
        $filepath = env('FILESPATH');
        $path = Storage::putFile('avatars', $file);

        if($path){
            return $filepath.$path;
        } else {
            return null;
        }
    }
}
