<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_visits', function (Blueprint $table) {
            $table->string('path');
            $table->date('date');
            $table->unsignedInteger('visits');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_visits');
    }
};
