<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        \Coyote\Plan::query()
            ->where('length', 40)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        \Coyote\Plan::query()
            ->where('length', 40)
            ->update(['is_active' => true]);
    }
};
