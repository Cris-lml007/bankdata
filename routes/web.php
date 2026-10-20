<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);


Route::prefix('/dashboard')
    ->middleware('auth')
    ->group(function () {

        // Panel de trabajo: todos los roles.
        Route::livewire('/', 'pages::dashboard.index')
            ->middleware('can:view-work-panel')
            ->name('dashboard');

        Route::livewire('/worker', 'pages::work.index')
            ->name('dashboard.worker');


        // Productos: Supervisor, Admin y Privilegio.
        Route::middleware('can:view-products')->group(function () {
            Route::livewire('/products', 'pages::product.index')
                ->name('dashboard.products');

            Route::livewire('/product/{product}', 'pages::product.view')
                ->name('dashboard.products.view');
        });


        // Categorías.
        Route::livewire('/categories', 'pages::category.index')
            ->middleware('can:view-categories')
            ->name('dashboard.categories');


        // Clientes.
        Route::middleware('can:view-customers')->group(function () {
            Route::livewire('/customers', 'pages::customers.index')
                ->name('dashboard.customers');

            Route::livewire('/customers/{customer}', 'pages::customers.view')
                ->name('dashboard.customers.view');
        });


        // Órdenes: contratos.
        Route::middleware('can:view-orders')->group(function () {
            Route::livewire('/contracts', 'pages::contract.index')
                ->name('dashboard.contracts');

            Route::livewire('/contracts/register', 'pages::contract.register')
                ->name('dashboard.contracts.register');

            Route::livewire('/contract/{contract}', 'pages::contract.view')
                ->name('dashboard.contracts.view');

            Route::livewire('/contract/{contract}/planning', 'pages::contract.planning')
                ->name('dashboard.contracts.planning');
        });


        // Reportes.
        Route::livewire('/reports', 'pages::reports.view')
            ->middleware('can:view-reports')
            ->name('dashboard.reports.view');


        // Cotizaciones: Admin y Privilegio.
        Route::middleware('can:manage-quotations')->group(function () {
            Route::livewire('/quotations', 'pages::quotation.index')
                ->name('dashboard.quotations');

            Route::livewire('/quotations/register', 'pages::quotation.register')
                ->name('dashboard.quotations.register');

            Route::livewire('/quotation/{quotation}', 'pages::quotation.view')
                ->name('dashboard.quotations.view');
        });


        // Producción: Admin y Privilegio.
        Route::middleware('can:manage-production')->group(function () {
            Route::livewire('/type-factories', 'pages::type-factory.index')
                ->name('dashboard.type-factories');

            Route::livewire('/industries', 'pages::industry.index')
                ->name('dashboard.industries');

            Route::livewire('/industries/register', 'pages::industry.register')
                ->name('dashboard.industries.register');

            Route::livewire('/industry/{industry}', 'pages::industry.view')
                ->name('dashboard.industries.view');
        });


        // Usuarios: Admin y Privilegio.
        Route::livewire('/users', 'pages::user.index')
            ->middleware('can:manage-users')
            ->name('dashboard.users');

        Route::livewire('/tasks', 'pages::tasks.index')
            ->middleware('can:view-work-panel')
            ->name('dashboard.tasks');


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
