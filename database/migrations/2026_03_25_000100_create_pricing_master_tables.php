<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->nullable();
                $table->string('code', 50);
                $table->string('name', 100);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['tenant_id', 'code']);
                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('units_of_measure')) {
            Schema::create('units_of_measure', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name', 50);
                $table->unsignedTinyInteger('precision')->default(3);
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

        if (!Schema::hasTable('price_list_items')) {
            Schema::create('price_list_items', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->nullable();
                $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->decimal('price', 12, 2);
                $table->decimal('min_qty', 12, 3)->nullable();
                $table->decimal('max_qty', 12, 3)->nullable();
                $table->timestamps();

                $table->unique(['price_list_id', 'product_id', 'min_qty', 'max_qty'], 'pli_unique');
                $table->index(['tenant_id', 'product_id']);
            });
        }

        if (Schema::hasTable('products')) {
            if (!Schema::hasColumn('products', 'category_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('category_id')->nullable()->after('category')->constrained('product_categories');
                });
            }

            if (!Schema::hasColumn('products', 'uom_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('uom_id')->nullable()->after('subcategory')->constrained('units_of_measure');
                });
            }

            if (!Schema::hasColumn('products', 'metadata')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->json('metadata')->nullable()->after('tags');
                });
            }

            if (!Schema::hasColumn('products', 'is_active')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('status');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'category_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('category_id');
                });
            }

            if (Schema::hasColumn('products', 'uom_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('uom_id');
                });
            }

            if (Schema::hasColumn('products', 'metadata')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('metadata');
                });
            }

            if (Schema::hasColumn('products', 'is_active')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
        }

        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('units_of_measure');
        Schema::dropIfExists('product_categories');
    }
};
