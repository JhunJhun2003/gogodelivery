<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('users')->cascadeOnDelete();
            $table->string('item_image')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('delivery_fees', 12, 2)->default(0);
            $table->string('recipient_name');
            $table->text('address');
            $table->string('phone_number', 30);
            $table->date('date');
            $table->text('remark')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ways');
    }
};