<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderLifecycleInventoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bulk_confirm_reserves_stock(): void
    {
        if (!Schema::hasTable('users')) {
            $this->markTestSkipped('Central test database schema is not available in this environment.');
        }

        $this->withoutMiddleware();

        $user = User::factory()->create();
        [$order, $stock] = $this->createOrderWithStock($user, 'pending', 10, 0, 3);

        $response = $this->actingAs($user)->post(route('central.processing.orders.bulk-status'), [
            'ids' => [$order->id],
            'status' => 'confirmed',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $stock->refresh();
        $order->refresh();

        $this->assertSame('confirmed', $order->status);
        $this->assertEquals(3.0, (float) $stock->reserve_quantity);
    }

    public function test_bulk_cancel_releases_reserved_stock(): void
    {
        if (!Schema::hasTable('users')) {
            $this->markTestSkipped('Central test database schema is not available in this environment.');
        }

        $this->withoutMiddleware();

        $user = User::factory()->create();
        [$order, $stock] = $this->createOrderWithStock($user, 'confirmed', 10, 3, 3);

        $response = $this->actingAs($user)->post(route('central.processing.orders.bulk-status'), [
            'ids' => [$order->id],
            'status' => 'cancelled',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $stock->refresh();
        $order->refresh();

        $this->assertSame('cancelled', $order->status);
        $this->assertEquals(0.0, (float) $stock->reserve_quantity);
    }

    private function createOrderWithStock(User $user, string $status, float $quantity, float $reserved, float $orderQty): array
    {
        $customer = Customer::create([
            'first_name' => 'Order',
            'last_name' => 'Customer',
            'mobile' => '9000000000',
            'email' => uniqid('customer_', true) . '@example.com',
            'type' => 'farmer',
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Warehouse ' . uniqid(),
            'code' => strtoupper(substr(uniqid('wh'), -6)),
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Product ' . uniqid(),
            'price' => 100,
            'manage_stock' => true,
            'is_active' => true,
            'is_sku_enabled' => true,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 100,
            'grand_total' => 100,
            'status' => $status,
            'placed_at' => now(),
            'created_by' => $user->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $orderQty,
            'unit_price' => 100,
            'total_price' => 100,
        ]);

        $stock = InventoryStock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'reserve_quantity' => $reserved,
        ]);

        $product->refreshStockOnHand();

        return [$order, $stock];
    }
}
