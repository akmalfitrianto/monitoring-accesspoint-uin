<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BuildingFloorPlan;
use App\Models\Building;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function() {
    Route::get('/admin/denah/{building}', BuildingFloorPlan::class)->name('admin.denah.building');
});

Route::get('/admin/api/buildings/{building}/rooms' , function (Building $building) {
    $building->load('rooms.accessPoints');
                                
    $rooms = $building->rooms->map(function ($room){
        return [
            'id' => $room->id,
            'code' => $room->code,
            'name' => $room->name,
            'x_position' => (float) $room->x_position,
            'y_position' => (float) $room->y_position,
            'width' => (float) $room->width,
            'height' => (float) $room->height,
        ];
    });

    $access_points = $building->rooms->flatMap(function($room){
        return $room->accessPoints->map(function($ap){
            return [
                'id' => $ap->id,
                'name' => $ap->name,
                'room_id' => $ap->room_id,
                'x_position' => (float)$ap->x_position,
                'y_position' => (float)$ap->y_position,
                'status' => $ap->status,
            ];
        });
    })->values();

    return response()->json([
        'rooms' => $rooms,
        'access_points' => $rooms,
    ]);
});
