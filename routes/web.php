<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::prefix('/dashboard')->group(function(){
    Route::livewire('/products','pages::product.index')->name('dashboard.products');
    Route::livewire('/product/{product}','pages::product.view')->name('dashboard.products.view');
    Route::livewire('/categories','pages::category.index')->name('dashboard.categories');
    Route::livewire('/type-factories','pages::type-factory.index')->name('dashboard.type-factories');


    Route::get('/products/files/{file}', function (\App\Models\File $file) {
        abort_unless(
            Storage::exists($file->path),
            404
        );

        return Storage::response(
            $file->path,
            $file->name,
            [
                'Content-Type' => $file->mime,
                'Content-Disposition' => 'inline; filename="' . $file->name . '"',
            ]
        );
    })->name('dashboard.products.files');

});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
