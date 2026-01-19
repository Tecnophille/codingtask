<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*' => 'string',
            'pickup_time' => 'required|date',
            'vip' => 'boolean', // Mapped to is_vip
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isVip = $request->input('vip', false);
        $capacity = env('KITCHEN_CAPACITY', 5);

        // Check capacity if not VIP
        if (!$isVip) {
            $activeCount = Order::active()->count();
            if ($activeCount >= $capacity) {
                return response()->json(['message' => 'Too Many Orders'], 429);
            }
        }

        $order = Order::create([
            'items' => $request->items,
            'pickup_time' => $request->pickup_time,
            'is_vip' => $isVip,
            'status' => 'active',
        ]);

        return response()->json($order, 201);
    }

    public function index()
    {
        $orders = Order::active()->get();
        return response()->json($orders);
    }

    public function complete($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->status = 'completed';
        $order->save();

        return response()->json(['message' => 'Order completed'], 200);
    }
}
