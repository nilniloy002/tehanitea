<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $t) {
            $t->json('gallery')->nullable();
            $t->text('ingredients')->nullable();
            $t->text('brewing_guide')->nullable();
            $t->text('storage_instructions')->nullable();
        });
        Schema::table('orders', function (Blueprint $t) { $t->uuid('public_token')->nullable()->unique(); $t->json('tax_snapshot')->nullable(); $t->string('coupon_code')->nullable(); });
        Schema::table('coupons', function (Blueprint $t) { $t->unsignedInteger('per_customer_limit')->nullable(); });
    }
    public function down(): void {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['gallery','ingredients','brewing_guide','storage_instructions']));
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['public_token','tax_snapshot','coupon_code']));
        Schema::table('coupons', fn (Blueprint $t) => $t->dropColumn('per_customer_limit'));
    }
};
