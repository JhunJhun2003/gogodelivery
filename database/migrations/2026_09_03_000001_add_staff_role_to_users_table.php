<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'shop', 'biker') NOT NULL DEFAULT 'shop'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'staff')->update(['role' => 'admin']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'shop', 'biker') NOT NULL DEFAULT 'shop'");
        }
    }
};
