<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return Review::latest()->get();
    }

    public function store(Request $request)
    {
        // Validasi input dari request
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'token' => 'required|string',
        ]);

        // Cek validitas token
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('review_token', $validated['token'])
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Tautan review tidak valid.'
            ], 403);
        }

        // Cek apakah review sudah ada untuk booking ini
        $existingReview = Review::where('booking_id', $validated['booking_id'])->first();
        if ($existingReview) {
            return response()->json([
                'message' => 'Review untuk booking ini sudah pernah dikirim.'
            ], 409);
        }

        // Hapus token dari data validasi agar tidak disimpan ke tabel review
        unset($validated['token']);

        // Simpan review
        $review = Review::create($validated);

        return response()->json([
            'message' => 'Review berhasil disimpan',
            'review' => $review
        ], 201);
    }
}
