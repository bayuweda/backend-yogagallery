<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');
    
        // Menemukan semua appointment yang tersedia berdasarkan tanggal
        $appointments = \App\Models\Appointment::where('date', $date)
                                               ->where('is_booked', false) // hanya slot yang belum dipesan
                                               ->get();
    
        if ($appointments->isEmpty()) {
            return response()->json(['available' => false]); // Jika tidak ada waktu yang tersedia
        }
    
        // Menyiapkan array dengan waktu yang tersedia
        $availableTimes = $appointments->pluck('time'); // Ambil hanya kolom waktu
    
        return response()->json([
            'available' => true,
            'availableTimes' => $availableTimes, // Mengembalikan daftar waktu yang tersedia
        ]);
    }
    

public function bookAppointment(Request $request)
{
    $date = $request->input('date');
    $time = $request->input('time');

    $appointment = \App\Models\Appointment::where('date', $date)
                                           ->where('time', $time)
                                           ->first();

    if ($appointment && !$appointment->is_booked) {
        $appointment->is_booked = true;
        $appointment->save();

        return response()->json(['message' => 'Appointment booked successfully!']);
    }

    return response()->json(['message' => 'Time slot is already booked or invalid'], 400);
}


}
