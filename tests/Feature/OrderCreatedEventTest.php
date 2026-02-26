<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderCreatedEventTest extends TestCase
{
    public function test_order_created_event_is_dispatched(): void
    {
        Event::fake();

        $product = Product::factory()->create(['active' => true]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        Event::assertDispatched(OrderCreated::class, function ($event) {
            return $event->order instanceof Order &&
                $event->order->status->value === 'CREATED';
        });
    }
}

