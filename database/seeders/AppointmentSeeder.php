<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        // Mulai dari 1 Januari 2025
        $start_date = Carbon::create(2025, 1, 1, 0, 0, 0); 
        // Sampai 31 Desember 2025
        $end_date = Carbon::create(2025, 12, 31, 23, 59, 59); 
        
        // Jam-jam yang tersedia untuk booking
        $time_slots = [
            '08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', 
            '16:00:00', '17:00:00', '18:00:00', '19:00:00', '20:00:00', '21:00:00'
        ];

        // Looping untuk setiap tanggal dari start_date hingga end_date
        while ($start_date <= $end_date) {
            foreach ($time_slots as $time) {
                // Menentukan waktu akhir (end_time) untuk setiap waktu mulai (start_time)
                $end_time = Carbon::createFromFormat('H:i:s', $time)->addHour()->format('H:i:s');
                
                DB::table('appointments')->insert([
                    'date' => $start_date->toDateString(), // Mengambil hanya tanggal
                    'start_time' => $time, // Menggunakan format waktu yang sesuai
                    'end_time' => $end_time, // Waktu akhir untuk booking
                    
                    'is_booked' => false,  // Awalnya status booking adalah false (belum dipesan)
                ]);
            }
            $start_date->addDay(); // Pindah ke hari berikutnya
        }
    }
}
