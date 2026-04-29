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
        Schema::create('tenant_invitations', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('role_id');
            $table->string('token')->unique();
            $table->string('status')->default('pending');
            $table->dateTime('expired_at');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invitations');
    }
};
