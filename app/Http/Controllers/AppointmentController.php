<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Appointment;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingNotification;
use Carbon\Carbon; // Menambahkan import Carbon
use Twilio\Rest\Client;
use Illuminate\Support\Str;


class AppointmentController extends Controller
{
    public function getBookings()
    {
        $bookings = Booking::orderBy('created_at', 'desc')->get();
        return response()->json($bookings);
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');

        $appointments = Appointment::where('date', $date)
            ->where('is_booked', false)
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json(['available' => false]);
        }

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
                'duration' => 'required|integer|min:1',
                'address' => 'required|string',
                'purposes' => 'required|array',
                'package_id' => 'required|exists:packages,id'
            ]);

            $startTime = $validated['time'];
            $duration = $validated['duration'];
            $timeslots = [];
            for ($i = 0; $i < $duration; $i++) {
                $timeslots[] = date("H:i", strtotime($startTime . ' + ' . $i . ' hour'));
            }

            $appointments = Appointment::where('date', $validated['date'])
                ->whereIn('start_time', $timeslots)
                ->where('is_booked', false)
                ->get();

            if ($appointments->count() === $duration) {
                foreach ($appointments as $appointment) {
                    $appointment->is_booked = true;
                    $appointment->save();
                }

                $booking = Booking::create([
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

                // ✅ Kirim notifikasi email ke admin
                Notification::route('mail', 'bayuweda24@gmail.com') // Ganti dengan email admin kamu
                    ->notify(new BookingNotification($booking));

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

    public function getTodayBookings()
    {
        $today = Carbon::today()->toDateString(); // Mendapatkan tanggal hari ini
        $bookings = Booking::whereDate('date', $today)->get();
        return response()->json($bookings);
    }

    public function markAsCompleted($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = 'completed';

        // Generate token unik untuk review
        $booking->review_token = Str::random(32); // Token unik 32 karakter
        $booking->save();

        $reviewUrl = url("http://localhost:3000/review?booking_id={$booking->id}&token={$booking->review_token}");

        $waNumber = preg_replace('/^0/', '62', $booking->phone);
        $message = "Halo {$booking->name}, terima kasih telah menggunakan layanan dari Yoga Gallery! Silakan isi review Anda di tautan berikut:\n{$reviewUrl}";
        $waLink = "https://wa.me/{$waNumber}?text=" . urlencode($message);

        return response()->json([
            'message' => 'Booking berhasil ditandai sebagai selesai dan link review dikirim.',
            'whatsapp_link' => $waLink,
            'booking' => $booking
        ]);
    }
}
