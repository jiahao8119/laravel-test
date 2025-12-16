<?php
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);           // list (filter + paginate)
    Route::post('/', [UserController::class, 'store']);          // create
    Route::get('/{user}', [UserController::class, 'show']);      // detail
    Route::put('/{user}', [UserController::class, 'update']);    // update
    Route::delete('/{user}', [UserController::class, 'destroy']);// soft delete

    Route::post('/bulk-delete', [UserController::class, 'bulkDelete']);
});
