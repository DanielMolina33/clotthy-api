<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::group(['middleware' => ['cors', 'json.response']], function(){
    Route::post("/register", "App\Http\Controllers\login\LoginController@register")->middleware('guest');
    Route::post("/signin/{userType}", "App\Http\Controllers\login\LoginController@signIn")->middleware('guest');
    Route::post("/password/forgot/{userType}", "App\Http\Controllers\login\LoginController@forgotPassword")->middleware('guest');
    Route::post("/password/reset/{userType}/{id}/{tokenId}", "App\Http\Controllers\login\LoginController@passwordReset")->middleware('guest');
});

Route::get('/logout-customers', function(Request $req){
    dd($req->user()->token()->revoke());
})->middleware('auth:customer');

Route::get('/logout-employees', function(Request $req){
    dd($req->user()->token()->revoke());
})->middleware('auth:employee');