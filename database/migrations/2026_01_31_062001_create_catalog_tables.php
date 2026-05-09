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
        | 0. TAX SYSTEM
        |--------------------------------------------------------------------------
        */

        Schema::create('tax_classes', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('name');
        });

        Schema::create('tax_rates', function (Blueprint $table) {

            $table->id();

            $table->foreignId('tax_class_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // GST 18%
            $table->decimal('rate', 8, 2);

            $table->string('zone')->nullable();

            $table->json('breakdown')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('tax_class_id');
            $table->index('rate');
            $table->index('zone');

            $table->index([
                'tax_class_id',
                'rate'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | 1. CATEGORIES
        |--------------------------------------------------------------------------
        */

        Schema::create('categories', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->text('description')->nullable();

            $table->string('banner_image')->nullable();

            $table->integer('sort_order')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_menu')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('name');
            $table->index('parent_id');

            $table->index('is_active');
            $table->index('is_featured');
            $table->index('is_menu');

            $table->index('sort_order');

            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'parent_id',
                'is_active'
            ]);

            $table->index([
                'is_menu',
                'is_active'
            ]);

            $table->index([
                'is_featured',
                'is_active'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText(
    ['name', 'description'],
    'categories_ft'
);
        });

        /*
        |--------------------------------------------------------------------------
        | 2. BRANDS
        |--------------------------------------------------------------------------
        */

        Schema::create('brands', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('website')->nullable();

            $table->text('description')->nullable();

            $table->string('logo')->nullable();

            $table->string('banner_image')->nullable();

            $table->integer('sort_order')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->string('country_of_origin')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('name');

            $table->index('is_active');
            $table->index('is_featured');

            $table->index('sort_order');

            $table->index('country_of_origin');

            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'is_featured',
                'is_active'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText(
    [
        'name',
        'description',
        'meta_title',
        'meta_description'
    ],
    'brands_ft'
);
        });

        /*
        |--------------------------------------------------------------------------
        | 3. PRODUCTS
        |--------------------------------------------------------------------------
        */

        Schema::create('products', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('sku')->unique()->nullable();

            $table->string('barcode')->nullable();

            $table->string('type')->default('simple');

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            $table->text('description')->nullable();

            $table->string('image')->nullable();

            $table->json('gallery')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 12, 2)->default(0);

            $table->decimal('mrp', 12, 2)->nullable();

            $table->decimal('cost_price', 12, 2)->nullable();

            $table->foreignId('tax_class_id')
                ->nullable()
                ->constrained('tax_classes')
                ->nullOnDelete();

            $table->decimal('tax_rate', 5, 2)->nullable();

            $table->string('hsn_code')->nullable();

            $table->string('default_discount_type')
                ->nullable()
                ->default('fixed');

            $table->decimal('default_discount_value', 15, 2)
                ->nullable()
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            $table->boolean('manage_stock')->default(true);

            $table->decimal('stock_on_hand', 12, 3)->default(0);

            $table->integer('min_order_qty')->default(1);

            $table->integer('reorder_level')->default(0);

            $table->string('unit_type')->default('piece');

            $table->string('packing_size')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Tax & Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_taxable')->default(true);

            $table->boolean('is_active')->default(true);

            $table->boolean('is_featured')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Agriculture Specifics
            |--------------------------------------------------------------------------
            */

            $table->date('harvest_date')->nullable();

            $table->date('expiry_date')->nullable();

            $table->string('technical_name')->nullable();

            $table->string('application_method')->nullable();

            $table->text('usage_instructions')->nullable();

            $table->json('target_crops')->nullable();

            $table->json('target_pests')->nullable();

            $table->string('pre_harvest_interval')->nullable();

            $table->string('shelf_life')->nullable();

            $table->string('origin')->nullable();

            $table->boolean('is_organic')->default(false);

            $table->string('certification_number')->nullable();

            $table->string('certificate_url')->nullable();

            /*
            |--------------------------------------------------------------------------
            | WMS Attributes
            |--------------------------------------------------------------------------
            */

            $table->decimal('weight', 8, 3)->nullable();

            $table->json('dimensions')->nullable();

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            $table->string('meta_title')->nullable();

            $table->text('meta_description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            // Search
            $table->index('name');

            $table->index('barcode');

            $table->index('type');

            // Relations
            $table->index('category_id');

            $table->index('brand_id');

            $table->index('tax_class_id');

            // Pricing
            $table->index('price');

            $table->index('mrp');

            $table->index('cost_price');

            $table->index('tax_rate');

            // Stock
            $table->index('stock_on_hand');

            $table->index('reorder_level');

            $table->index('manage_stock');

            // Status
            $table->index('is_active');

            $table->index('is_featured');

            $table->index('is_taxable');

            $table->index('is_organic');

            // Agriculture
            $table->index('harvest_date');

            $table->index('expiry_date');

            $table->index('origin');

            $table->index('technical_name');

            // SEO
            $table->index('hsn_code');

            // Soft Deletes
            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Catalog Filtering
            $table->index([
                'category_id',
                'is_active'
            ]);

            $table->index([
                'brand_id',
                'is_active'
            ]);

            // Featured Products
            $table->index([
                'is_featured',
                'is_active'
            ]);

            // Inventory
            $table->index([
                'manage_stock',
                'stock_on_hand'
            ]);

            // Expiry Management
            $table->index([
                'expiry_date',
                'is_active'
            ]);

            // Product Listing
            $table->index([
                'type',
                'is_active'
            ]);

            // Agriculture
            $table->index([
                'is_organic',
                'is_active'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText(
    [
        'name',
        'description',
        'technical_name',
        'usage_instructions',
        'meta_title',
        'meta_description'
    ],
    'products_ft'
);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT IMAGES
        |--------------------------------------------------------------------------
        */

        Schema::create('product_images', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image_path');

            $table->integer('sort_order')->default(0);

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('product_id');

            $table->index('sort_order');

            $table->index('is_primary');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_id',
                'is_primary'
            ]);

            $table->index([
                'product_id',
                'sort_order'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT OPTIONS
        |--------------------------------------------------------------------------
        */

        Schema::create('product_options', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('product_id');

            $table->index('name');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_id',
                'name'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT OPTION VALUES
        |--------------------------------------------------------------------------
        */

        Schema::create('product_option_values', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_option_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('value');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('product_option_id');

            $table->index('value');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_option_id',
                'value'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT VARIANTS
        |--------------------------------------------------------------------------
        */

        Schema::create('product_variants', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('sku')->unique();

            $table->decimal('price', 12, 2)->nullable();

            $table->decimal('stock_quantity', 12, 3)->default(0);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('product_id');

            $table->index('price');

            $table->index('stock_quantity');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_id',
                'stock_quantity'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | PRODUCT VARIANT OPTION VALUES
        |--------------------------------------------------------------------------
        */

        Schema::create('product_variant_option_values', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_option_value_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('product_variant_id');

            $table->index('product_option_value_id');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
    [
        'product_variant_id',
        'product_option_value_id'
    ],
    'pvov_unique_idx'
);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_classes');
    }
};