<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return Review::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'name' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $review = Review::create($validated);

        return response()->json(['message' => 'Review berhasil disimpan', 'review' => $review], 201);
    }
}
