<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function getBookings()
    {
        // Ambil semua data booking terbaru
        $bookings = Booking::orderBy('created_at', 'desc')->get();

        return response()->json($bookings);
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');

        // Ambil semua slot appointment yang tersedia
        $appointments = Appointment::where('date', $date)
            ->where('is_booked', false)
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json(['available' => false]);
        }

        // Ambil waktu mulai yang tersedia
        $availableTimes = $appointments->pluck('start_time');

        return response()->json([
            'available' => true,
            'availableTimes' => $availableTimes,
        ]);
    }

    public function bookAppointment(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'date' => 'required|date',
                'time' => 'required|string',
                'duration' => 'required|integer|min:1', // Durasi dalam jam
                'address' => 'required|string',
                'purposes' => 'required|array',
                'package_id' => 'required|exists:packages,id'
            ]);

            // Hitung slot waktu berdasarkan durasi
            $startTime = $validated['time'];
            $duration = $validated['duration'];
            $timeslots = [];
            for ($i = 0; $i < $duration; $i++) {
                $timeslots[] = date("H:i", strtotime($startTime . ' + ' . $i . ' hour'));
            }

            // Cek apakah semua slot waktu tersedia
            $appointments = Appointment::where('date', $validated['date'])
                ->whereIn('start_time', $timeslots)
                ->where('is_booked', false)
                ->get();

            if ($appointments->count() === $duration) {
                // Tandai semua slot waktu sebagai dipesan
                foreach ($appointments as $appointment) {
                    $appointment->is_booked = true;
                    $appointment->save();
                }

                // Simpan booking
                Booking::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'date' => $validated['date'],
                    'start_time' => $startTime,
                    'end_time' => date("H:i", strtotime($startTime . ' + ' . $duration . ' hour')),
                    'address' => $validated['address'],
                    'purposes' => json_encode($validated['purposes']),
                    'package_id' => $validated['package_id'],

                ]);

                return response()->json([
                    'message' => 'Appointment booked successfully!',
                    'time_range' => "$startTime - " . date("H:i", strtotime($startTime . ' + ' . $duration . ' hour'))
                ]);
            }

            return response()->json(['message' => 'Time slots are already booked or invalid'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
