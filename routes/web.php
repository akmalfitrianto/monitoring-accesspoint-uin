<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BuildingFloorPlan;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function() {
    Route::get('/admin/denah/{building}', BuildingFloorPlan::class)->name('admin.denah.building');
});


