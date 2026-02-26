<?php

namespace App\ValueObject;

use App\Enums\OrderStatus;

class CreateOrderValueObject
{
    private array $orderItems;
    private float $totalPrice;
    private OrderStatus $status;

    /**
     * @param array $orderItemsData
     */
    public function __construct(array $orderItemsData)
    {
        $this->setOrderItems($orderItemsData);
        $this->setTotalPrice();
        $this->setStatus();
    }

    /**
     * @param array $orderItemsData
     * @return void
     */
    private function setOrderItems(array $orderItemsData): void
    {
        foreach ($orderItemsData as $itemData) {
            $this->orderItems[] = new CreateOrderItemValueObject(
                $itemData['product_id'],
                $itemData['quantity'],
            );
        }
    }

    /**
     * @return array
     */
    public function getOrderItems(): array
    {
        return $this->orderItems;
    }

    /**
     * @return void
     */
    private function setTotalPrice(): void
    {
        $this->totalPrice = 0;
        /** @var CreateOrderItemValueObject $item */
        foreach ($this->orderItems as $item) {
            $unitPrice = $item->getUnitPrice();
            $this->totalPrice += round($unitPrice * $item->getQuantity(), 2);
        }
        $this->totalPrice = round($this->totalPrice, 2);
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * @return void
     */
    private function setStatus(): void
    {
        $this->status = OrderStatus::CREATED;
    }

    /**
     * @return OrderStatus
     */
    public function getStatus(): OrderStatus
    {
        return $this->status;
    }
}
