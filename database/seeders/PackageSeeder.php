<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   

public function run()
{
    $packages = [
        [
            'name' => 'Standar',
            'price' => 500000,
            'duration' => 1,
            'total_photos' => 50,
            'edited_photos' => 20,
            'includes' => json_encode(['File asli dalam format digital juga disertakan.']),
            'suitable_for' => 'Cocok untuk: Sesi foto pribadi, potret profesional, atau dokumentasi singkat.'
        ],
        [
            'name' => 'Premium',
            'price' => 1000000,
            'duration' => 2,
            'total_photos' => 100,
            'edited_photos' => 40,
            'includes' => json_encode(['File asli dalam format digital juga disertakan.', 'Cetak 10 lembar']),
            'suitable_for' => 'Cocok untuk sesi keluarga, prewedding, atau proyek profesional.'
        ],
        [
            'name' => 'Eksklusif',
            'price' => 2000000,
            'duration' => 3,
            'total_photos' => 200,
            'edited_photos' => 80,
            'includes' => json_encode(['File asli dalam format digital juga disertakan.', 'Album cetak 20 halaman']),
            'suitable_for' => 'Paket ini cocok untuk event besar seperti pernikahan atau konser.'
        ]
    ];

    foreach ($packages as $package) {
        Package::create($package);
    }
}

}
