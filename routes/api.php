<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

# /api/v1.0/
Route::group([
    "namespace" => "App\Http\Controllers\Api\V1_0",
    "prefix" => "/v1.0",
    "as" => "api.v1_0.",
], static function () {
    //    # /api/v1.0/auth
    //    Route::group([
    //        "namespace" => "Auth",
    //        "prefix" => "/auth",
    //    ], static function () {
    //        Route::post("/registration", "AuthController@registration")->name("registration");
    //        Route::post("/login", "AuthController@login")->name("login");
    //    });

    #/api/v1.0/buckets
    Route::group([
        "namespace" => "Bucket",
        "prefix" => "/buckets",
        "as" => "buckets.",
    ], static function () {
        Route::get("/list", "BucketCRUDController@bucketList")->name("bucket-list");
        Route::post("/create", "BucketCRUDController@createBucket")->name("create-bucket");
        Route::delete("/delete", "BucketCRUDController@deleteBucket")->name("delete-bucket");
    });

    #/api/v1.0/storage
    Route::group([
        "namespace" => "Storage",
        "prefix" => "/storage",
        "as" => "storage.",
    ], static function () {
        Route::post("/upload", "StorageController@upload")->name("upload");
        Route::post("/simpleUpload", "StorageController@simpleUpload")->name("simple-upload");
        Route::post("/createDirectory", "StorageController@createDirectory")->name("create_directory");
        Route::post("/view", "StorageController@view")->name("view");
        Route::post("/move", "StorageController@move")->name("move");
        Route::post("/moveDirectory", "StorageController@moveDirectory")->name("moveDirectory");
        Route::delete("/delete", "StorageController@delete")->name("delete");
    });
});


# /api/v1.0/
//Route::group([
//    "namespace" => "App\Http\Controllers\Api\V1_0",
//    "prefix" => "/v1.0",
//    "as" => "api.v1_0.",
//    "middleware" => ["auth:sanctum"],
//], static function () {
//    # /api/v1.0/auth
//    Route::group([
//        "namespace" => "Auth",
//        "prefix" => "/auth",
//    ], static function () {
//        Route::post("/logout", "AuthController@logout")->name("logout");
//        Route::get("/profile", "AuthController@profile")->name("profile");
//    });
//});
