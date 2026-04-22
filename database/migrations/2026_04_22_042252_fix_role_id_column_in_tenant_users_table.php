<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });

        Schema::table('tenant_users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::table('tenant_users', function (Blueprint $table) {
            $table->uuid('role_id')->nullable()->after('user_id');
        });
    }
};
