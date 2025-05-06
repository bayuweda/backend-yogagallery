<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi (fillable)
    protected $fillable = ['date', 'time', 'is_booked'];

    // Casting kolom untuk memastikan tipe data yang tepat
    protected $casts = [
        'is_booked' => 'boolean', // Menyimpan status boolean dengan benar
        'date' => 'date', // Mengonversi tanggal ke format yang sesuai
    ];

    // Jika ingin menambahkan custom accessor untuk waktu atau status lainnya,
    // Anda dapat menambahkannya di sini.
}
