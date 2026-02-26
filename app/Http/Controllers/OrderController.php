<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Service\OrderService;
use App\ValueObject\CreateOrderValueObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService = new OrderService()
    )
    {}

    /**
     * @OA\PathItem(path="/orders")
     *
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Pobierz wszystkie zamówienia",
     *     description="Zwraca listę wszystkich zamówień z ich produktami",
     *     @OA\Response(
     *         response=200,
     *         description="Lista zamówień",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="CREATED"),
     *                     @OA\Property(property="total_price", type="number", format="float", example=150.50),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-02-26 14:30:00"),
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="product_id", type="integer", example=1),
     *                             @OA\Property(property="quantity", type="integer", example=2),
     *                             @OA\Property(property="unit_price", type="number", format="float", example=75.25),
     *                             @OA\Property(
     *                                 property="product",
     *                                 type="object",
     *                                 @OA\Property(property="name", type="string", example="Produkt XYZ"),
     *                                 @OA\Property(property="sku", type="string", example="SKU-1234"),
     *                                 @OA\Property(property="active", type="boolean", example=true)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $orders = $this->orderService->getAllOrders();
        return OrderResource::collection($orders);
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Utwórz nowe zamówienie",
     *     description="Tworzy nowe zamówienie z listą produktów",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=1, description="ID produktu (musi istnieć i być aktywny)"),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Ilość (większa niż 0)")
     *                 ),
     *                 example={
     *                     {"product_id": 1, "quantity": 2},
     *                     {"product_id": 2, "quantity": 5}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Zamówienie utworzone pomyślnie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="CREATED"),
     *                 @OA\Property(property="total_price", type="number", format="float", example=150.50),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-02-26 14:30:00"),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="unit_price", type="number", format="float", example=75.25)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The items field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items.0.product_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="Produkt nie istnieje lub nie jest aktywny.")
     *                 ),
     *                 @OA\Property(
     *                     property="items.0.quantity",
     *                     type="array",
     *                     @OA\Items(type="string", example="Ilość musi być większa niż 0.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Błąd serwera",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Nie udało się stworzyć zamówienia")
     *         )
     *     )
     * )
     */
    public function store(OrderStoreRequest $request): JsonResponse|OrderResource
    {
        $validatedData = $request->validated();
        $orderCreateValueObject = new CreateOrderValueObject((array)$validatedData['items']);

        try {
            $order = $this->orderService->storeOrderData($orderCreateValueObject);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Nie udało się stworzyć zamówienia'], 500);
        }

        return new OrderResource($order);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Pobierz szczegóły zamówienia",
     *     description="Zwraca szczegóły konkretnego zamówienia wraz z produktami",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID zamówienia",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Szczegóły zamówienia",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="CREATED"),
     *                 @OA\Property(property="total_price", type="number", format="float", example=150.50),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-02-26 14:30:00"),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="unit_price", type="number", format="float", example=75.25),
     *                         @OA\Property(
     *                             property="product",
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="Produkt XYZ"),
     *                             @OA\Property(property="sku", type="string", example="SKU-1234"),
     *                             @OA\Property(property="active", type="boolean", example=true)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Zamówienie nie znalezione",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Order] 123")
     *         )
     *     )
     * )
     */
    public function show(Order $order): JsonResponse|OrderResource
    {
        return new OrderResource($order);
    }
}
