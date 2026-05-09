<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        /*
        |--------------------------------------------------------------------------
        | 0. EXPENSES
        |--------------------------------------------------------------------------
        */

        Schema::create('expenses', function (Blueprint $table) {

            $table->id();

            $table->string('description');

            $table->decimal('amount', 12, 2);

            $table->date('date');

            $table->string('category')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('date');

            $table->index('category');

            $table->index('created_by');

            $table->index('amount');

            $table->index([
                'category',
                'date'
            ]);

            $table->fullText([
                'description'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | 1. PRICE LISTS
        |--------------------------------------------------------------------------
        */

        Schema::create('price_lists', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('currency')->default('USD');

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('name');

            $table->index('currency');

            $table->index('is_default');
        });

        /*
        |--------------------------------------------------------------------------
        | 2. ORDERS
        |--------------------------------------------------------------------------
        */

        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            $table->string('order_number')->unique();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')->constrained();

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->string('status')->default('pending');

            $table->string('verification_status')->default('unverified');

            $table->string('payment_status')->default('unpaid');

            $table->string('shipping_status')->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Financials
            |--------------------------------------------------------------------------
            */

            $table->decimal('total_amount', 12, 2)->default(0);

            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->string('discount_type')->default('fixed');

            $table->decimal('discount_value', 12, 2)->default(0);

            $table->decimal('shipping_amount', 12, 2)->default(0);

            $table->decimal('grand_total', 12, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Methods & Tracking
            |--------------------------------------------------------------------------
            */

            $table->string('payment_method')->nullable();

            $table->string('shipping_method')->nullable();

            $table->string('discount_code')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address Snapshots
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('billing_address_id')->nullable();

            $table->unsignedBigInteger('shipping_address_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Data
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->timestamp('placed_at')->useCurrent();

            $table->timestamp('scheduled_at')->nullable();

            $table->boolean('is_future_order')->default(false);

            $table->json('tags')->nullable();

            $table->string('currency', 10)->default('INR');

            $table->string('channel', 20)->default('web');

            /*
            |--------------------------------------------------------------------------
            | Lifecycle Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('cancelled_at')->nullable();

            $table->string('cancel_reason')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            // Relations
            $table->index('customer_id');

            $table->index('warehouse_id');

            // Status
            $table->index('status');

            $table->index('verification_status');

            $table->index('payment_status');

            $table->index('shipping_status');

            // Financials
            $table->index('grand_total');

            $table->index('placed_at');

            $table->index('scheduled_at');

            $table->index('is_future_order');

            // Channels
            $table->index('channel');

            $table->index('currency');

            // Audit
            $table->index('created_by');

            $table->index('updated_by');

            $table->index('completed_by');

            $table->index('cancelled_by');

            // Soft Deletes
            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Customer Orders
            $table->index([
                'customer_id',
                'status'
            ]);

            // Payment Dashboard
            $table->index([
                'payment_status',
                'placed_at'
            ]);

            // Shipping Dashboard
            $table->index([
                'shipping_status',
                'placed_at'
            ]);

            // Warehouse Allocation
            $table->index([
                'warehouse_id',
                'status'
            ]);

            // Future Orders
            $table->index([
                'is_future_order',
                'scheduled_at'
            ]);

            // Order Analytics
            $table->index([
                'channel',
                'placed_at'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'notes',
                'cancel_reason'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | ORDER ITEMS
        |--------------------------------------------------------------------------
        */

        Schema::create('order_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained();

            $table->string('sku');

            $table->string('product_name');

            $table->decimal('quantity', 12, 3);

            $table->decimal('unit_price', 12, 2);

            $table->string('discount_type')->default('fixed');

            $table->decimal('discount_value', 12, 2)->default(0);

            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->decimal('cost_price', 12, 2)->nullable();

            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->decimal('total_price', 12, 2);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('order_id');

            $table->index('product_id');

            $table->index('sku');

            $table->index('quantity');

            $table->index('unit_price');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'order_id',
                'product_id'
            ]);

            $table->index([
                'product_id',
                'quantity'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'product_name'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | ORDER ADDRESSES
        |--------------------------------------------------------------------------
        */

        Schema::create('order_addresses', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('name');

            $table->string('address_line_1');

            $table->string('address_line_2')->nullable();

            $table->string('city');

            $table->string('state')->nullable();

            $table->string('postal_code')->nullable();

            $table->string('country');

            $table->string('phone')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('order_id');

            $table->index('type');

            $table->index('city');

            $table->index('state');

            $table->index('postal_code');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'order_id',
                'type'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText(
    [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state'
    ],
    'order_addr_ft'
);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT PRICES
        |--------------------------------------------------------------------------
        */

        Schema::create('product_prices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('price_list_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);

            $table->decimal('sale_price', 12, 2)->nullable();

            $table->date('sale_start')->nullable();

            $table->date('sale_end')->nullable();

            $table->integer('min_quantity')->default(1);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('product_id');

            $table->index('price_list_id');

            $table->index('sale_start');

            $table->index('sale_end');

            $table->index('min_quantity');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'product_id',
                'price_list_id',
                'min_quantity'
            ]);

            $table->index([
                'sale_start',
                'sale_end'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | COUPONS
        |--------------------------------------------------------------------------
        */

        Schema::create('coupons', function (Blueprint $table) {

            $table->id();

            $table->string('code')->unique();

            $table->string('description')->nullable();

            $table->string('type')->default('fixed');

            $table->decimal('amount', 12, 2);

            $table->integer('usage_limit')->nullable();

            $table->integer('usage_count')->default(0);

            $table->date('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('expires_at');

            $table->index('is_active');

            $table->index('usage_count');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'is_active',
                'expires_at'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | COUPON USAGES
        |--------------------------------------------------------------------------
        */

        Schema::create('coupon_usages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('coupon_id')->constrained();

            $table->foreignId('customer_id')->constrained();

            $table->foreignId('order_id')->constrained();

            $table->timestamp('used_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('coupon_id');

            $table->index('customer_id');

            $table->index('order_id');

            $table->index('used_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'coupon_id',
                'customer_id'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | SHIPMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('shipments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')->constrained();

            $table->foreignId('warehouse_id')->constrained();

            $table->string('tracking_number')->nullable();

            $table->string('carrier')->nullable();

            $table->string('status')->default('shipped');

            $table->string('shipping_label_url')->nullable();

            $table->decimal('weight', 8, 3)->nullable();

            $table->timestamp('shipped_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->date('estimated_delivery_date')->nullable();

            $table->unsignedInteger('packages_count')->default(1);

            $table->string('dimensions', 50)->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('order_id');

            $table->index('warehouse_id');

            $table->index('tracking_number');

            $table->index('carrier');

            $table->index('status');

            $table->index('shipped_at');

            $table->index('delivered_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'status',
                'shipped_at'
            ]);

            $table->index([
                'warehouse_id',
                'status'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | INVOICES
        |--------------------------------------------------------------------------
        */

        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('order_id')->constrained();

            $table->date('issue_date');

            $table->date('due_date')->nullable();

            $table->decimal('total_amount', 12, 2);

            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->string('status')->default('draft');

            $table->string('gstin', 20)->nullable();

            $table->string('pdf_path')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('order_id');

            $table->index('issue_date');

            $table->index('due_date');

            $table->index('status');

            $table->index('gstin');

            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'status',
                'due_date'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'notes'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | PAYMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained();

            $table->foreignId('order_id')->constrained();

            $table->string('method');

            $table->string('transaction_id')->nullable();

            $table->decimal('amount', 12, 2);

            $table->timestamp('paid_at')->useCurrent();

            $table->text('notes')->nullable();

            $table->string('gateway', 30)->nullable();

            $table->json('gateway_response')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('invoice_id');

            $table->index('order_id');

            $table->index('method');

            $table->index('transaction_id');

            $table->index('paid_at');

            $table->index('gateway');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'method',
                'paid_at'
            ]);

            $table->index([
                'gateway',
                'paid_at'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'notes'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | RETURNS
        |--------------------------------------------------------------------------
        */

        Schema::create('returns', function (Blueprint $table) {

            $table->id();

            $table->string('rma_number')->unique();

            $table->foreignId('order_id')->constrained();

            $table->foreignId('customer_id')->constrained();

            $table->string('status')->default('requested');

            $table->text('reason')->nullable();

            $table->string('refund_method')->nullable();

            $table->foreignId('inspected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('inspected_at')->nullable();

            $table->decimal('refunded_amount', 12, 2)->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('order_id');

            $table->index('customer_id');

            $table->index('status');

            $table->index('refund_method');

            $table->index('inspected_by');

            $table->index('inspected_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'customer_id',
                'status'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'reason'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | RETURN ITEMS
        |--------------------------------------------------------------------------
        */

        Schema::create('return_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('return_id')
                ->constrained('returns')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained();

            $table->decimal('quantity', 12, 3);

            $table->decimal('quantity_received', 12, 3)->nullable();

            $table->string('condition')->default('sellable');

            $table->string('condition_received')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('return_id');

            $table->index('product_id');

            $table->index('condition');

            $table->index('condition_received');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'return_id',
                'product_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('expenses');
    }
};