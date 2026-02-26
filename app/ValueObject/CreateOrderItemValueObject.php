<?php

namespace App\ValueObject;

use App\Models\Product;

class CreateOrderItemValueObject
{
    private Product $product;

    /**
     * @param int $product_id
     * @param int $quantity
     */
    public function __construct(
        private readonly int $product_id,
        private readonly int $quantity
    )
    {
        $this->product = Product::find($this->product_id);
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->product_id;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->product->price;
    }
}
