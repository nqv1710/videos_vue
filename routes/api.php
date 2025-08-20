<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\S2VideoController;
use App\Http\Controllers\S2CommentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get-user', [UserController::class, 'user']);

Route::post('/user-by-token', [UserController::class, 'userByToken']);

Route::get('/videos', [S2VideoController::class, 'index']);
Route::get('/comments', [S2CommentController::class, 'get_by_video']);
Route::post('/comments', [S2CommentController::class, 'store']);

Route::get('/videos-list', [S2VideoController::class, 'listVideo']);
Route::post('/video/action', [S2VideoController::class, 'storeOrUpdate']);


// Detail comments via query params: /api/detail_comments?id=<videoId>
Route::get('/detail_comments', [S2CommentController::class, 'getComments']);
