<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectController;

Route::prefix("/v1")->group(function () {

    Route::middleware('auth:sanctum')->group(function () {


        Route::apiResource('/projects', ProjectController::class)->whereUuid('project');;

        Route::apiResource('projects.tasks', TaskController::class)
            ->only(['index', 'store'])
            ->whereUuid('project');


        Route::apiResource('tasks', TaskController::class)
            ->except(['index', 'store'])
            ->whereUuid('task');

    });

    Route::prefix("/auth")->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware("auth:sanctum");

    });

});





