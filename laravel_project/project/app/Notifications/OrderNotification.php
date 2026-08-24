<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $title,
        public string $message,
        public string $icon = 'bi-bell',
        public string $color = '#3b82f6',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'order',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'title'        => $this->title,
            'message'      => $this->message,
            'icon'         => $this->icon,
            'color'        => $this->color,
            'url'          => route('orders.show', $this->order->id),
        ];
    }
}
