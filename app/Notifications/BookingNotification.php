<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    private $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }


    public function toArray($notifiable): array
    {
        return [
            'subject' => 'A new booking has been made',
            'name' => $this->booking->name,
            'address' => $this->booking->address,
            'phone' => $this->booking->phone,
            'date' => $this->booking->date,
            'total_price' => $this->booking->total_price,



        ];
    }
}
