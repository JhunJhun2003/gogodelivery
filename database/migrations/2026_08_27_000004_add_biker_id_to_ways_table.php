<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ways', function (Blueprint $table) {
            $table->foreignId('biker_id')->nullable()->after('shop_id')->constrained('bikers')->nullOnDelete();
            $table->index(['date', 'biker_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ways', function (Blueprint $table) {
            $table->dropForeign(['biker_id']);
            $table->dropIndex(['date', 'biker_id']);
            $table->dropColumn('biker_id');
        });
    }
};