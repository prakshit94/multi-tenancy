<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReturnBulkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_preview_handles_ineligible_orders_without_runtime_error(): void
    {
        $this->withoutMiddleware();

        Permission::firstOrCreate(['name' => 'returns edit', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('returns edit');

        $customer = Customer::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'mobile' => '9999999999',
            'email' => 'returns@example.com',
            'type' => 'farmer',
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
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
            'status' => 'pending',
            'placed_at' => now(),
            'created_by' => $user->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'returns.csv',
            "order_id,sku,quantity,reason,condition\n{$order->id},,1,Test,sellable\n"
        );

        $response = $this->actingAs($user)->postJson(route('central.returns.bulk-preview'), [
            'csv_file' => $file,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('rows.0.preview_status', 'error');

        $this->assertStringContainsString('Ineligible status', $response->json('rows.0.preview_message'));
        $this->assertSame($order->id, $response->json('rows.0.order_id'));
    }
}
