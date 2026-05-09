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
        | 2. WMS (Warehouses & Inventory)
        |--------------------------------------------------------------------------
        */

        Schema::create('warehouses', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('code')->unique();

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->text('address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (Performance Optimized)
            |--------------------------------------------------------------------------
            */

            // Search
            $table->index('name');

            $table->index('email');

            $table->index('phone');

            // Status
            $table->index('is_default');

            $table->index('is_active');

            // Soft Deletes
            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Active Warehouses
            $table->index([
                'is_active',
                'is_default'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'name',
                'address'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | INVENTORY STOCKS
        |--------------------------------------------------------------------------
        */

        Schema::create('inventory_stocks', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Inventory Quantities
            |--------------------------------------------------------------------------
            */

            $table->decimal('quantity', 12, 3)->default(0);

            $table->decimal('reserve_quantity', 12, 3)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Unique Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'warehouse_id',
                'product_id'
            ]);

            /*
            |--------------------------------------------------------------------------
            | INDEXES (Performance Optimized)
            |--------------------------------------------------------------------------
            */

            // Relations
            $table->index('warehouse_id');

            $table->index('product_id');

            // Stock Queries
            $table->index('quantity');

            $table->index('reserve_quantity');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Product Stock Lookup
            $table->index([
                'product_id',
                'warehouse_id'
            ]);

            // Low Stock Reports
            $table->index([
                'product_id',
                'quantity'
            ]);

            // Reserved Stock Reports
            $table->index([
                'warehouse_id',
                'reserve_quantity'
            ]);

            // Warehouse Inventory Dashboard
            $table->index([
                'warehouse_id',
                'quantity'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | INVENTORY MOVEMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('inventory_movements', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('stock_id')
                ->constrained('inventory_stocks')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Movement Information
            |--------------------------------------------------------------------------
            */

            $table->string('type');
            // purchase, sale, transfer, adjustment, return

            $table->decimal('quantity', 12, 3);
            // Negative for decrease, Positive for increase

            $table->unsignedBigInteger('reference_id')->nullable();
            // Order ID, PO ID

            $table->string('reason')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            // Who performed movement

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (Performance Optimized)
            |--------------------------------------------------------------------------
            */

            // Relations
            $table->index('stock_id');

            $table->index('reference_id');

            $table->index('user_id');

            // Movement Queries
            $table->index('type');

            $table->index('quantity');

            $table->index('created_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Stock History
            $table->index([
                'stock_id',
                'created_at'
            ]);

            // Movement Type Reports
            $table->index([
                'type',
                'created_at'
            ]);

            // User Activity
            $table->index([
                'user_id',
                'created_at'
            ]);

            // Reference Tracking
            $table->index([
                'reference_id',
                'type'
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('warehouses');
    }
};