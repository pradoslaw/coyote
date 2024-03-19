<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings_key_value', function (Blueprint $table) {
            $table->text('key');
            $table->text('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_key_value');
    }
};
