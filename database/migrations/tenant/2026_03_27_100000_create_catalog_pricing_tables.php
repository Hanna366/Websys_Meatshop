<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('price_lists')) {
            Schema::create('price_lists', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->nullable();
                $table->string('code', 100);
                $table->string('name', 150);
                $table->string('channel', 50)->default('retail');
                $table->string('currency', 3)->default('PHP');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'code']);
                $table->index(['tenant_id', 'channel', 'status', 'effective_from']);
            });
        }

        if (!Schema::hasTable('product_prices')) {
            Schema::create('product_prices', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->nullable();
                $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('uom', 20)->default('kg');
                $table->decimal('price', 12, 2);
                $table->timestamps();

                $table->unique(['price_list_id', 'product_id', 'uom'], 'product_prices_unique');
                $table->index(['tenant_id', 'product_id']);
            });
        }

        if (Schema::hasTable('products')) {
            if (!Schema::hasColumn('products', 'category_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('category_id')->nullable()->after('category');
                });
            }

            if (!Schema::hasColumn('products', 'uom_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('uom_id')->nullable()->after('subcategory');
                });
            }

            if (Schema::hasTable('categories')) {
                try {
                    Schema::table('products', function (Blueprint $table) {
                        $table->dropForeign(['category_id']);
                    });
                } catch (\Throwable $e) {
                    // Ignore when no foreign key exists yet.
                }

                try {
                    Schema::table('products', function (Blueprint $table) {
                        $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // Ignore when already constrained.
                }
            }

            if (Schema::hasTable('units_of_measure')) {
                try {
                    Schema::table('products', function (Blueprint $table) {
                        $table->dropForeign(['uom_id']);
                    });
                } catch (\Throwable $e) {
                    // Ignore when no foreign key exists yet.
                }

                try {
                    Schema::table('products', function (Blueprint $table) {
                        $table->foreign('uom_id')->references('id')->on('units_of_measure')->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // Ignore when already constrained.
                }
            }
        }

        if (Schema::hasTable('product_categories') && Schema::hasTable('categories')) {
            $legacyCategories = DB::table('product_categories')->get();
            foreach ($legacyCategories as $legacyCategory) {
                DB::table('categories')->updateOrInsert(
                    ['code' => $legacyCategory->code],
                    [
                        'name' => $legacyCategory->name,
                        'sort_order' => (int) ($legacyCategory->sort_order ?? 0),
                        'is_active' => (bool) ($legacyCategory->is_active ?? true),
                        'updated_at' => now(),
                        'created_at' => $legacyCategory->created_at ?? now(),
                    ]
                );
            }
        }

        if (Schema::hasTable('price_list_items') && Schema::hasTable('product_prices')) {
            $legacyPrices = DB::table('price_list_items')->get();
            foreach ($legacyPrices as $legacyPrice) {
                DB::table('product_prices')->updateOrInsert(
                    [
                        'price_list_id' => $legacyPrice->price_list_id,
                        'product_id' => $legacyPrice->product_id,
                        'uom' => 'kg',
                    ],
                    [
                        'tenant_id' => $legacyPrice->tenant_id,
                        'price' => $legacyPrice->price,
                        'updated_at' => now(),
                        'created_at' => $legacyPrice->created_at ?? now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropForeign(['category_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when no foreign key exists.
            }

            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropForeign(['uom_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when no foreign key exists.
            }
        }

        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('categories');
    }
};
