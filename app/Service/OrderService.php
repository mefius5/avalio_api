<?php

namespace App\Service;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\ValueObject\CreateOrderItemValueObject;
use App\ValueObject\CreateOrderValueObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{

    /**
     * @throws Throwable
     */
    public function storeOrderData(CreateOrderValueObject $orderCreateValueObject): Order
    {
        return DB::transaction(function () use ($orderCreateValueObject) {
            $order = $this->createOrder($orderCreateValueObject);

            /** @var CreateOrderItemValueObject $item */
            foreach ($orderCreateValueObject->getOrderItems() as $item) {
                $this->createOrderItem($item, $order);
            }

            OrderCreated::dispatch($order);

            return $order;
        });
    }

    /**
     * @param CreateOrderValueObject $orderCreateValueObject
     * @return Order
     */
    private function createOrder(CreateOrderValueObject $orderCreateValueObject): Order
    {
        $order = new Order();
        $order->total_price = $orderCreateValueObject->getTotalPrice();
        $order->status = $orderCreateValueObject->getStatus();
        $order->save();

        return $order;
    }

    /**
     * @param CreateOrderItemValueObject $item
     * @param Order $order
     * @return void
     */
    private function createOrderItem(CreateOrderItemValueObject $item, Order $order): void
    {
        $orderItem = new OrderItem();
        $orderItem->order_id = $order->id;
        $orderItem->product_id = $item->getProductId();
        $orderItem->quantity = $item->getQuantity();
        $orderItem->unit_price = $item->getUnitPrice();
        $orderItem->save();
    }

    /**
     * @return Collection
     */
    public function getAllOrders(): Collection
    {
        return Order::get();
    }
}
