<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // ID untuk tiap entri
            $table->date('date');  // Tanggal booking
            $table->time('start_time');  // Jam mulai booking
            $table->time('end_time');    // Jam selesai booking
            $table->boolean('is_booked')->default(false);  // Status booking (false = available, true = booked)
            $table->timestamps();  // Timestamps untuk created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');  // Menghapus tabel appointments jika rollback
    }
};
