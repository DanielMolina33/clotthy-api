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

// Employees routes
Route::group(['middleware' => ['cors', 'json.response', 'auth:employee']], function(){
    // Person
    Route::resource("person", "App\Http\Controllers\person\PersonController")->except(['create', 'edit']);

    // Roles
    Route::resource("role", "App\Http\Controllers\sRole\sRoleController")->except(['create', 'edit']);
    Route::resource("module", "App\Http\Controllers\sRole\ModuleController")->except(['create', 'edit']);
    Route::resource("module_role", "App\Http\Controllers\sRole\ModuleRoleController")->except(['create', 'edit']);

});

// Special and free routes
Route::group(['middleware' => ['cors', 'json.response']], function(){
    // Profile
    Route::get("/profile/me", "App\Http\Controllers\person\PersonController@loadMyProfile");
    Route::post("/profile/update", "App\Http\Controllers\person\PersonController@updateMyProfile");

    // Countries
    Route::resource("country", "App\Http\Controllers\country\CountryController")->except(['create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Departments
    Route::resource("department", "App\Http\Controllers\department\DepartmentController")->except(['create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Cities
    Route::resource("city", "App\Http\Controllers\city\CityController")->except(['create', 'store', 'show', 'edit', 'update', 'destroy']);
});

// Logout routes

// Customers
Route::get('/logout-customers', function(Request $req){
    if(!Auth::guard('customer')->check()){
        $response = [false, 400];
    } else {
        Auth::guard('customer')->user()->token()->revoke();
        $response = [true, 200];
    }

    return response()->json($response[0], $response[1]);
});

// Employees
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
