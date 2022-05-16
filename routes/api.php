<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Login routes
Route::group(['middleware' => ['cors', 'json.response']], function(){
    Route::post("/register", "App\Http\Controllers\login\LoginController@register")->middleware('guest');
    Route::post("/signin/{userType}", "App\Http\Controllers\login\LoginController@signIn")->middleware('guest');
    Route::post("/password/forgot/{userType}", "App\Http\Controllers\login\LoginController@forgotPassword")->middleware('guest');
    Route::post("/password/reset/{userType}/{id}/{tokenId}", "App\Http\Controllers\login\LoginController@passwordReset");
});

//Employees routes
Route::group(['middleware' => ['cors', 'json.response', 'auth:employee']], function(){
    Route::resource("person", "App\Http\Controllers\person\PersonController")->except(['create', 'edit']);
    Route::resource("parameter", "App\Http\Controllers\parameter\ParameterController")->except(['create', 'edit', 'show']);
    Route::resource("parameter_value", "App\Http\Controllers\parameter\ParameterValueController")->except(['create', 'edit', 'show']);
    Route::resource("company", "App\Http\Controllers\company\CompanyController");
});

// Public routes
Route::group(['middleware' => ['cors', 'json.response']], function(){
    Route::resource("pqrsf", "App\Http\Controllers\pqrsf\PqrsfController");
    Route::get("/my_pqrsf", "App\Http\Controllers\pqrsf\PqrsfController@myPqrsf");
});

// Modificar respuestas
Route::get('/logout-customers', function(Request $req){
    dd($req->user()->token()->revoke());
})->middleware('auth:customer');

Route::get('/logout-employees', function(Request $req){
    dd($req->user()->token()->revoke());
})->middleware('auth:employee');

Route::fallback(function(){
    return response()->json(['message' => 'Not Found'], 404);
});