<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;
use Modules\Blog\Models\Redirect;

Route::middleware('web')->group(function (): void {
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    // Before the post routes, which would otherwise read these as a post or a category.
    Route::get('/blog/feed', [BlogController::class, 'feed'])->name('blog.feed');
    // An unknown slug may be one the category or tag used to have.
    Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category')
        ->missing(fn (Request $request) => Redirect::responseFor($request));
    Route::get('/blog/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag')
        ->missing(fn (Request $request) => Redirect::responseFor($request));
    Route::get('/blog/preview/{post}', [BlogController::class, 'preview'])->name('blog.preview');
    Route::get('/blog/{category}/{slug}', [BlogController::class, 'show'])->name('blog.show.category');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
});
