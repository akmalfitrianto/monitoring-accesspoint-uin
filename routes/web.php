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

Route::get('/admin/api/buildings/{building}/rooms', function (Building $building) {
    $building->load('rooms.accessPoints');

    $rooms = $building->rooms->map(function ($room){
        return [
            'id' => $room->id,
            'code' => $room->code,
            'name' => $room->name,
            'x_position' => (float) $room->x_position / 10,
            'y_position' => (float) $room->y_position/ 10,
            'width' => (float) $room->width,
            'height' => (float) $room->height,
            'floor' => (int) $room->floor,
        ];
    });

    $access_points = $building->rooms->flatMap(function($room){
        return $room->accessPoints->map(function($ap){
            return [
                'id' => $ap->id,
                'name' => $ap->name,
                'room_id' => $ap->room_id,
                'x_position' => (float)$ap->x_position / 10,
                'y_position' => (float)$ap->y_position / 10,
                'status' => $ap->status,
                'floor' => (int) $ap->floor,
            ];
        });
    })->values();

    return response()->json([
        'rooms' => $rooms,
        'access_points' => $access_points,
        'total_floors' => $building->total_floors,
    ]);
});
