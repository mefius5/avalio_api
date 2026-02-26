<?php

namespace Tests\Unit;

use App\Models\Product;
use App\ValueObject\CreateOrderValueObject;
use Tests\TestCase;

class TotalPriceTest extends TestCase
{
    public function test_total_price_with_single_product(): void
    {
        Product::factory()->create([
            'id' => 1,
            'price' => 10.50,
            'active' => true,
        ]);

        $orderItemsData = [
            ['product_id' => 1, 'quantity' => 2],
        ];

        $orderValueObject = new CreateOrderValueObject($orderItemsData);
        $this->assertEquals(21.00, $orderValueObject->getTotalPrice());
    }

    public function test_total_price_with_multiple_products(): void
    {
        Product::factory()->create([
            'id' => 1,
            'price' => 15.99,
            'active' => true,
        ]);

        Product::factory()->create([
            'id' => 2,
            'price' => 5.50,
            'active' => true,
        ]);

        Product::factory()->create([
            'id' => 3,
            'price' => 100.00,
            'active' => true,
        ]);

        $orderItemsData = [
            ['product_id' => 1, 'quantity' => 3],  // 15.99 * 3 = 47.97
            ['product_id' => 2, 'quantity' => 5],  // 5.50 * 5 = 27.50
            ['product_id' => 3, 'quantity' => 1],  // 100.00 * 1 = 100.00
        ];

        $orderValueObject = new CreateOrderValueObject($orderItemsData);
        // Assert: 47.97 + 27.50 + 100.00 = 175.47
        $this->assertEquals(175.47, $orderValueObject->getTotalPrice());
    }

    public function test_total_price_with_decimal_precision(): void
    {
        Product::factory()->create([
            'id' => 1,
            'price' => 10.99,
            'active' => true,
        ]);

        $orderItemsData = [
            ['product_id' => 1, 'quantity' => 7],
        ];

        $orderValueObject = new CreateOrderValueObject($orderItemsData);
        $this->assertEquals(76.93, $orderValueObject->getTotalPrice());
    }
}
