<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');
    
        // Menemukan semua appointment yang tersedia berdasarkan tanggal
        $appointments = Appointment::where('date', $date)
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'date' => 'required|date',
            'time' => 'required|string',
            'address' => 'required|string',
            'purposes' => 'required|array',
        ]);

        $appointment = Appointment::where('date', $validated['date'])
                                   ->where('time', $validated['time'])
                                   ->first();

        if ($appointment && !$appointment->is_booked) {
            $appointment->is_booked = true;
            $appointment->save();

            $booking = new Booking();
            $booking->name = $validated['name'];
            $booking->email = $validated['email'];
            $booking->phone = $validated['phone'];
            $booking->date = $validated['date'];
            $booking->time = $validated['time'];
            $booking->address = $validated['address'];
            $booking->purposes = json_encode($validated['purposes']);
            $booking->save();

            return response()->json(['message' => 'Appointment booked successfully!']);
        }

        return response()->json(['message' => 'Time slot is already booked or invalid'], 400);
    }
}
