<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Login routes
Route::group(['middleware' => ['cors', 'json.response']], function(){
    // Register
    Route::post("/register", "App\Http\Controllers\login\LoginController@register")->middleware('guest:customer');

    // Signin
    Route::post("/signin/{userType}", "App\Http\Controllers\login\LoginController@signIn")->middleware('guest:employee,customer');

    // Password Recovery
    Route::post("/password/forgot/{userType}", "App\Http\Controllers\login\LoginController@forgotPassword")->middleware('guest:employee,customer');
    Route::post("/password/reset/{userType}/{id}/{tokenId}", "App\Http\Controllers\login\LoginController@passwordReset");
});

//Employees routes
Route::group(['middleware' => ['cors', 'json.response', 'auth:employee']], function(){
    // Person
    Route::resource("person", "App\Http\Controllers\person\PersonController")->except(['create', 'edit']);

    // Parameters
    Route::resource("parameter", "App\Http\Controllers\parameter\ParameterController")->except(['create', 'edit', 'show']);
    Route::resource("parameter_value", "App\Http\Controllers\parameter\ParameterValueController")->except(['create', 'edit', 'show']);

    // Company
    Route::resource("company", "App\Http\Controllers\company\CompanyController")->except(['create', 'edit', 'destroy']);

    // Roles
    Route::resource("role", "App\Http\Controllers\sRole\sRoleController")->except(['create', 'edit']);
    Route::resource("module", "App\Http\Controllers\sRole\ModuleController")->except(['create', 'edit']);
    Route::resource("module_role", "App\Http\Controllers\sRole\ModuleRoleController")->except(['create', 'edit']);

    // Products
    Route::resource("product", "App\Http\Controllers\product\ProductController")->except(['create', 'edit']);
    Route::resource("inventory", "App\Http\Controllers\product\InventoryController")->except(['create', 'edit', 'destroy']);
    Route::resource("order", "App\Http\Controllers\product\OrderController")->except(['create', 'edit', 'destroy']);
});

// Pqrsf routes
Route::group(['middleware' => ['cors', 'json.response']], function(){
    // Admin
    Route::resource("pqrsf", "App\Http\Controllers\pqrsf\PqrsfController")->except(['create', 'edit', 'destroy']);

    // User's pqrsf
    Route::get("/my_pqrsf", "App\Http\Controllers\pqrsf\PqrsfController@myPqrsf");
});

Route::get('/logout-customers', function(Request $req){
    if(!Auth::guard('customer')->check()){
        $response = [false, 400];
    } else {
        Auth::guard('customer')->user()->token()->revoke();
        $response = [true, 200];
    }
    
    return response()->json($response[0], $response[1]);
});

Route::get('/logout-employees', function(Request $req){
    if(!Auth::guard('employee')->check()){
        $response = [false, 400];
    } else {
        Auth::guard('employee')->user()->token()->revoke();
        $response = [true, 200];
    }
    
    return response()->json($response[0], $response[1]);
});

// Route not found
Route::fallback(function(){
    return response()->json(['message' => 'Route not found'], 404);
});