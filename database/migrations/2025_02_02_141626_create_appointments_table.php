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
        $table->id();
        $table->date('date');  // Tanggal booking
        $table->time('time');  // Jam booking
        $table->boolean('is_booked')->default(false);  // Menyimpan status booking (false = available, true = booked)
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
