<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trial_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Coyote\User::class);
            $table->string('stage');
            $table->string('assortment');
            $table->boolean('badge_narrow');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_sessions');
    }
};
