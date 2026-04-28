<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('activity');
    }

    public function down(): void
    {
        // Intentionally empty — restoring this orphaned table is not needed.
    }
};
