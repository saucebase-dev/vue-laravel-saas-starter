<?php

use Illuminate\Support\Facades\Route;
use Modules\ComingSoon\Http\Controllers\ComingSoonController;

Route::middleware('web')->group(function (): void {
    Route::get(config('coming-soon.path'), [ComingSoonController::class, 'index'])->name('coming-soon.index');
});
