<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('way_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('way_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('remark')->nullable();
            $table->string('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('way_status_histories');
    }
};
