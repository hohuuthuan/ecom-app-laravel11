<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing([
            'items.product',
            'shipment',
            'user',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đặt hàng thành công #' . $this->order->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
