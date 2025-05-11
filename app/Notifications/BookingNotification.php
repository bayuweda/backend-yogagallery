<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingNotification extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Baru Masuk')
            ->greeting('Halo Admin,')
            ->line('Ada booking baru dengan detail sebagai berikut:')
            ->line('Nama: ' . $this->booking->name)
            ->line('Email: ' . $this->booking->email)
            ->line('Telepon: ' . $this->booking->phone)
            ->line('Tanggal: ' . $this->booking->date)
            ->line('Jam: ' . $this->booking->start_time . ' - ' . $this->booking->end_time)
            ->line('Alamat: ' . $this->booking->address)
            ->line('Tujuan: ' . implode(', ', json_decode($this->booking->purposes, true)))  // Jika purposes disimpan sebagai JSON
            ->line('ID Paket: ' . $this->booking->package_id)
            ->salutation('Terima kasih.');
    }
}
