<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_store_order(): void
    {
        $product = Product::factory()->create(['active' => true]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);
        $response->assertStatus(201);

        $orderId = $response->json('data.id');
        $this->assertNotNull($orderId);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'CREATED',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_store_order_with_invalid_quantity(): void
    {
        $product = Product::factory()->create(['active' => true]);
        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_store_order_with_inactive_product(): void
    {
        $product = Product::factory()->create(['active' => false]);
        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_store_order_with_empty_items(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_show_order(): void
    {
        $product = Product::factory()->create(['active' => true]);
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals($order->id, $responseData['id']);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'total_price',
                'created_at',
                'items' => [
                    '*' => [
                        'product',
                        'product',
                        'quantity',
                        'unit_price',
                    ]
                ]
            ]
        ]);
    }

    public function test_show_order_not_found(): void
    {
        $response = $this->getJson('/api/orders/' . 9999);
        $response->assertStatus(404);
    }

    public function test_show_all_orders(): void
    {
        $product = Product::factory()->create(['active' => true]);
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson('/api/orders');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'total_price',
                    'created_at',
                    'items' => [
                        '*' => [
                            'product',
                            'product',
                            'quantity',
                            'unit_price',
                        ]
                    ]
                ]
            ]
        ]);
    }

}
