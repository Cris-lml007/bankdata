<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::prefix('/dashboard')->group(function(){
    Route::livewire('/','pages::dashboard.index')->name('dashboard');
    Route::livewire('/products','pages::product.index')->name('dashboard.products');
    Route::livewire('/product/{product}','pages::product.view')->name('dashboard.products.view');
    Route::livewire('/categories','pages::category.index')->name('dashboard.categories');
    Route::livewire('/type-factories','pages::type-factory.index')->name('dashboard.type-factories');
    Route::livewire('/quotations','pages::quotation.index')->name('dashboard.quotations');
    Route::livewire('/quotations/register','pages::quotation.register')->name('dashboard.quotations.register');
    Route::livewire('/quotation/{quotation}','pages::quotation.view')->name('dashboard.quotations.view');
    Route::livewire('/contracts','pages::contract.index')->name('dashboard.contracts');
    Route::livewire('/contracts/register','pages::contract.register')->name('dashboard.contracts.register');
    Route::livewire('/contract/{contract}','pages::contract.view')->name('dashboard.contracts.view');
    Route::livewire('/contract/{contract}/planning','pages::contract.planning')->name('dashboard.contracts.planning');
    Route::livewire('/industries','pages::industry.index')->name('dashboard.industries');
    Route::livewire('/industries/register','pages::industry.register')->name('dashboard.industries.register');
    Route::livewire('/industry/{industry}','pages::industry.view')->name('dashboard.industries.view');
    Route::livewire('/users','pages::user.index')->name('dashboard.users');

    Route::livewire('/worker','pages::work.index')->name('dashboard.worker');



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
