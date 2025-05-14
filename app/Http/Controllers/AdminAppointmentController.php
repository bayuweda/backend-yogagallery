<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AdminAppointmentController extends Controller
{
    public function approve($id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan'], 404);
        }

        if ($booking->status === 'approved') {
            return response()->json(['message' => 'Booking sudah disetujui'], 400);
        }

        $booking->status = 'approved';
        $booking->save();

        return response()->json(['message' => 'Booking berhasil disetujui']);
    }

    public function createSlot(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
        ]);

        $exists = Appointment::where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Slot already exists'], 409);
        }

        $slot = Appointment::create([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'is_booked' => false,
        ]);

        return response()->json(['message' => 'Slot created successfully', 'slot' => $slot]);
    }

    public function getSlots(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');



        $query = Appointment::with('booking');

        if ($start && $end) {
            $query->whereBetween('date', [$start, $end]);
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
