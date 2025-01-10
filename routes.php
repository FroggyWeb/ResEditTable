<?php

use EvolutionCMS\Resedittable\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('', function () {return '';})
    ->name('resedittable::index');

Route::get('show/{container}/{folder?}', [Controller::class, 'show'])
    ->whereNumber('container')
    ->whereNumber('folder')
    ->name('resedittable::show');

// Route::post('getList', [Controller::class, 'getList']);

Route::post('action', [Controller::class, 'action'])
    ->name('resedittable::action');

// Route::post('limit/{container}', [Controller::class, 'setLimit'])
//     ->whereNumber('container')
//     ->name('resedittable::limit');
