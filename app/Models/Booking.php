<?php

// App\Models\Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // Properti fillable
    protected $fillable = [
        'name',
        'email',
        'phone',
        'date',
        'start_time',
        'end_time',
        'address',
        'purposes',
        'package_id',
    ];

    // Relasi dengan Package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
