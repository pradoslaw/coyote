<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteTrackTable extends Migration
{
    public function up(): void
    {
        Schema::create('route_track', function (Blueprint $table): void {
            $table->text('route');
            $table->text('method');
            $table->integer('count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_track');
    }
}
