<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // ✅ Gunakan kolom yang benar sesuai controller
    protected $fillable = ['date', 'start_time', 'end_time', 'is_booked', 'booking_id'];

    protected $casts = [
        'is_booked' => 'boolean',
        'date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
