<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        $start_date = Carbon::create(2025, 1, 1, 0, 0, 0); // Mulai dari 1 Januari 2024
        $end_date = Carbon::create(2025, 12, 31, 23, 59, 59); // Sampai 31 Desember 2024
        $time_slots = [
            '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
        ];

        while ($start_date <= $end_date) {
            foreach ($time_slots as $time) {
                DB::table('appointments')->insert([
                    'date' => $start_date->toDateString(),
                    'time' => $time,
                    'is_booked' => false,  // Awalnya waktu tersedia
                ]);
            }
            $start_date->addDay(); // Pindah ke hari berikutnya
        }
    }
}

