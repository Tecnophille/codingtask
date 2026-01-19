<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order()
    {
        $response = $this->postJson('/api/orders', [
            'items' => ['burger', 'fries'],
            'pickup_time' => '2025-09-26T12:30:00Z',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['status' => 'active']);
    }

    public function test_kitchen_capacity_throttling()
    {
        // Fill kitchen to capacity (5)
        Order::factory()->count(5)->create(['status' => 'active']);

        $response = $this->postJson('/api/orders', [
            'items' => ['burger'],
            'pickup_time' => '2025-09-26T12:30:00Z',
        ]);

        $response->assertStatus(429);
    }

    public function test_vip_bypass_capacity()
    {
        // Fill kitchen to capacity (5)
        Order::factory()->count(5)->create(['status' => 'active']);

        $response = $this->postJson('/api/orders', [
            'items' => ['caviar'],
            'pickup_time' => '2025-09-26T12:30:00Z',
            'vip' => true,
        ]);

        $response->assertStatus(201);
    }

    public function test_can_list_active_orders()
    {
        Order::factory()->create(['status' => 'active']);
        Order::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/orders/active');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_can_complete_order()
    {
        $order = Order::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/orders/{$order->id}/complete");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_completing_order_frees_capacity()
    {
        // Fill kitchen
        $orders = Order::factory()->count(5)->create(['status' => 'active']);
        $firstOrder = $orders->first();

        // Verify full
        $this->postJson('/api/orders', [
            'items' => ['burger'],
            'pickup_time' => '2025-09-26T12:30:00Z',
        ])->assertStatus(429);

        // Complete one
        $this->postJson("/api/orders/{$firstOrder->id}/complete");

        // Verify space available
        $this->postJson('/api/orders', [
            'items' => ['burger'],
            'pickup_time' => '2025-09-26T12:30:00Z',
        ])->assertStatus(201);
    }
}
