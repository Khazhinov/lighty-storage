<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

#/
Route::group([
    "namespace" => "App\Http\Controllers\Web",
    "prefix" => "/",
    "as" => "web.",
], static function () {
    #/storage
    Route::group([
        "namespace" => "Storage",
        "prefix" => "/storage",
        "as" => "storage.",
    ], static function () {
        Route::get("/download/{bucket}", "StorageController@download")->name("download");
    });
});
