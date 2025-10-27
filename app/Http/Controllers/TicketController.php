<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AccessPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function getTechnicians()
    {
        $technicians = User::role('teknisi')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json($technicians);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'access_point_id' => 'required|exists:access_points,id',
            'description' => 'required|string|max:1000',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $technicians = User::find($validated['assigned_to']);
        if(!$technicians->hasRole('teknisi')) {
            return response()->json([
                'message' => 'User yang dipilih bukan teknisi',
            ], 422);
        }

        $ap = \App\Models\AccessPoint::find($validated['access_point_id']);
        $ap->update(['status' => 'maintenance']);

        $ticket = Ticket::create([
            'title' => "Laporan masalah pada {$ap->name}",
            'access_point_id' => $validated['access_point_id'],
            'building_id' => $ap->building_id,
            'room_id' => $ap->room_id,
            'description' => $validated['description'],
            'status' => 'open',
            'reported_by' => Auth::id(),
            'assigned_to' => $validated['assigned_to'], 
        ]);

         return response()->json([
            'message' => 'Tiket berhasil dibuat',
        ], 201);
    }
}
