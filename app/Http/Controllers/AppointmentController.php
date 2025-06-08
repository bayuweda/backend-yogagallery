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
use Illuminate\Support\Facades\Validator;
 use Illuminate\Support\Facades\Log;


class AppointmentController extends Controller
{

    public function show($id)
    {
        $booking = Booking::with('package')->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan'], 404);
        }

        // Decode purposes jika bentuknya JSON string
        if (is_string($booking->purposes)) {
            $decoded = json_decode($booking->purposes, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $booking->purposes = $decoded;
            }
        }

        return response()->json($booking);
    }
    public function getBookings()
    {
        $bookings = Booking::orderBy('created_at', 'desc')->get();
        return response()->json($bookings);
    }

    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');

        // Ambil semua appointment di tanggal tersebut
        $appointments = Appointment::where('date', $date)->get();

        // Booking yang sudah diambil orang lain (is_booked = true)
        $bookedAppointments = $appointments->where('is_booked', true)->values();

        // Slot yang tersedia (is_booked = false)
        $availableAppointments = $appointments->where('is_booked', false)->values();

        if ($availableAppointments->isEmpty()) {
            return response()->json(['available' => false]);
        }

        return response()->json([
            'available' => true,
            'availableTimes' => $availableAppointments->pluck('start_time'),
            'bookedTimes' => $bookedAppointments->map(function ($item) {
                return [
                    'start' => $item->start_time,
                    'duration' => $item->duration ?? 1, // Default 1 jam jika tidak ada kolom duration
                ];
            })->values(),
        ]);
    }


    // public function bookAppointment(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|email',
    //             'phone' => 'required|string|max:20',
    //             'date' => 'required|date',
    //             'time' => 'required|string',
    //             'duration' => 'required|integer|min:1',
    //             'address' => 'required|string',
    //             'purposes' => 'required|array',
    //             'package_id' => 'required|exists:packages,id'
    //         ]);

    //         $startTime = $validated['time'];
    //         $duration = $validated['duration'];
    //         $timeslots = [];
    //         for ($i = 0; $i < $duration; $i++) {
    //             $timeslots[] = date("H:i", strtotime($startTime . ' + ' . $i . ' hour'));
    //         }

    //         $appointments = Appointment::where('date', $validated['date'])
    //             ->whereIn('start_time', $timeslots)
    //             ->where('is_booked', false)
    //             ->get();

    //         if ($appointments->count() === $duration) {
    //             // Buat booking terlebih dahulu
    //             $booking = Booking::create([
    //                 'name' => $validated['name'],
    //                 'email' => $validated['email'],
    //                 'phone' => $validated['phone'],
    //                 'date' => $validated['date'],
    //                 'start_time' => $startTime,
    //                 'end_time' => date("H:i", strtotime($startTime . ' + ' . $duration . ' hour')),
    //                 'address' => $validated['address'],
    //                 'purposes' => json_encode($validated['purposes']),
    //                 'package_id' => $validated['package_id'],
    //             ]);

    //             // Tandai setiap appointment sebagai booked dan kaitkan dengan booking_id
    //             foreach ($appointments as $appointment) {
    //                 $appointment->is_booked = true;
    //                 $appointment->booking_id = $booking->id;
    //                 $appointment->save();
    //             }

    //             // ✅ Kirim notifikasi email ke admin
    //             Notification::route('mail', 'bayuweda24@gmail.com') // Ganti dengan email admin kamu
    //                 ->notify(new BookingNotification($booking));

    //             return response()->json([
    //                 'message' => 'Appointment booked successfully!',
    //                 'time_range' => "$startTime - " . date("H:i", strtotime($startTime . ' + ' . $duration . ' hour'))
    //             ]);
    //         }

    //         return response()->json(['message' => 'Time slots are already booked or invalid'], 400);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
   

public function bookAppointment(Request $request)
{
    try {
        Log::debug('Booking request input', $request->all());

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

        Log::debug('Validated data', $validated);

        $packageExists = \App\Models\Package::find($validated['package_id']);
        Log::debug('Package found', ['package' => $packageExists]);

        $startTime = $validated['time'];
        $duration = $validated['duration'];
        $timeslots = [];
        for ($i = 0; $i < $duration; $i++) {
            $timeslots[] = date("H:i", strtotime($startTime . ' + ' . $i . ' hour'));
        }

        Log::debug('Generated timeslots', $timeslots);

        $appointments = Appointment::where('date', $validated['date'])
            ->whereIn('start_time', $timeslots)
            ->where('is_booked', false)
            ->get();

        Log::debug('Available appointments found', ['count' => $appointments->count(), 'appointments' => $appointments->toArray()]);

        if ($appointments->count() === $duration) {
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

            foreach ($appointments as $appointment) {
                $appointment->is_booked = true;
                $appointment->booking_id = $booking->id;
                $appointment->save();
            }

            Notification::route('mail', 'bayuweda24@gmail.com')
                ->notify(new BookingNotification($booking));

            Log::debug('Booking successful', ['booking_id' => $booking->id]);

            return response()->json([
                'message' => 'Appointment booked successfully!',
                'time_range' => "$startTime - " . date("H:i", strtotime($startTime . ' + ' . $duration . ' hour'))
            ]);
        }

        Log::debug('Booking failed - time slots are already booked or invalid', ['requested_slots' => $timeslots]);

        return response()->json(['message' => 'Time slots are already booked or invalid'], 400);
    } catch (\Exception $e) {
        Log::error('Booking exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function getTodayBookings()
    {
        $today = Carbon::today()->toDateString(); // Mendapatkan tanggal hari ini
        $bookings = Booking::whereDate('date', $today)->get();
        return response()->json($bookings);
    }

    public function getWeeklyBookings()
    {
        // Ambil hari Senin minggu ini
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        // Ambil hari Minggu minggu ini
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        // Ambil booking dalam rentang Senin - Minggu
        $bookings = Booking::whereBetween('date', [$startOfWeek, $endOfWeek])->get();

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


    public function generateWeeklyAppointments(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'week' => 'nullable|in:this,next',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Parameter "week" harus bernilai this atau next.'], 422);
        }

        // Tentukan minggu (this week atau next week)
        $week = $request->input('week', 'this');
        $startDate = $week === 'next'
            ? Carbon::now()->addWeek()->startOfWeek()
            : Carbon::now()->startOfWeek();

        $endDate = $startDate->copy()->endOfWeek();

        // Waktu slot
        $timeSlots = [
            '08:00:00',
            '09:00:00',
            '10:00:00',
            '11:00:00',
            '12:00:00',
            '13:00:00',
            '14:00:00',
            '15:00:00',
            '16:00:00',
            '17:00:00',
            '18:00:00',
            '19:00:00',
            '20:00:00',
            '21:00:00'
        ];

        // Ambil semua data yang sudah ada dalam range minggu ini
        $existing = Appointment::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($item) {
                return $item->date . '_' . $item->start_time;
            })->toArray();

        $newSlots = [];

        // Generate slot baru jika belum ada
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($timeSlots as $startTime) {
                $key = $date->toDateString() . '_' . $startTime;

                if (!in_array($key, $existing)) {
                    $endTime = Carbon::createFromFormat('H:i:s', $startTime)->addHour()->format('H:i:s');

                    $newSlots[] = [
                        'date' => $date->toDateString(),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_booked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Batch insert
        if (!empty($newSlots)) {
            Appointment::insert($newSlots);
        }

        return response()->json([
            'message' => 'Slot minggu ' . ($week === 'next' ? 'depan' : 'ini') . ' berhasil digenerate.',
            'total_slots_created' => count($newSlots)
        ]);
    }


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
}
