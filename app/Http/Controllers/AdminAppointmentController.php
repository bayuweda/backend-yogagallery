<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;

class AdminAppointmentController extends Controller
{

public function createSlot(Request $request)
{
    $validated = $request->validate([
        'date' => 'required|date',
        'start_time' => 'required', // boleh tetap tanpa format ketat jika pakai Carbon::parse
    ]);

    try {
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addHour();

        $slot = Appointment::create([
            'date' => $validated['date'],
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'is_booked' => false, // kalau ada kolom ini di DB-mu
        ]);

        return response()->json([
            'message' => 'Slot berhasil dibuat.',
            'slot' => $slot,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat membuat slot.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    public function getSlots(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $date = $request->query('date');

        $query = Appointment::with('booking');

        if ($start && $end) {
            $query->whereBetween('date', [$start, $end]);
        } elseif ($date) {
            $query->whereDate('date', $date);
        }

        $slots = $query->orderBy('date')->orderBy('start_time')->get();

        return response()->json($slots);
    }








    public function deleteSlot($id)
    {
        $slot = Appointment::findOrFail($id);

        if ($slot->is_booked) {
            return response()->json(['message' => 'Cannot delete booked slot'], 403);
        }

        $slot->delete();
        return response()->json(['message' => 'Slot deleted successfully']);
    }
}
