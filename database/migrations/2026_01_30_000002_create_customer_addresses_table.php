<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Address Type & Control
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [
                'billing',
                'shipping',
                'both'
            ])->default('shipping');

            $table->string('label')->nullable(); // Home, Office, Farm, Warehouse

            /*
            |--------------------------------------------------------------------------
            | Contact Details
            |--------------------------------------------------------------------------
            */

            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address Information
            |--------------------------------------------------------------------------
            */

            $table->string('address_line1');
            $table->string('address_line2')->nullable();

            $table->string('village')->nullable();
            $table->string('taluka')->nullable();
            $table->string('district')->nullable();

            $table->string('state')->nullable();
            $table->string('country')->default('India');

            $table->string('pincode', 10)->nullable();
            $table->string('post_office')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Geo Location
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Flags
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

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

            // Foreign Key
            $table->index('customer_id');

            // Address Search
            $table->index('state');
            $table->index('district');
            $table->index('taluka');
            $table->index('village');
            $table->index('pincode');

            // Contact Search
            $table->index('contact_phone');

            // Address Type
            $table->index('type');

            // Default Address Lookup
            $table->index('is_default');

            // Soft Deletes
            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Fast customer address retrieval
            $table->index([
                'customer_id',
                'type'
            ]);

            // Default address query
            $table->index([
                'customer_id',
                'is_default'
            ]);

            // Regional filtering
            $table->index([
                'state',
                'district'
            ]);

            // Deep regional filtering
            $table->index([
                'state',
                'district',
                'taluka'
            ]);

            // Pincode search
            $table->index([
                'pincode',
                'state'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fulltext Search
            |--------------------------------------------------------------------------
            |
            | Useful for large-scale address searching
            | Requires MySQL 5.7+ / MySQL 8+
            |
            */

           $table->fullText(
    [
        'address_line1',
        'address_line2',
        'village',
        'taluka',
        'district',
        'state',
        'post_office'
    ],
    'cust_addr_ft'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};