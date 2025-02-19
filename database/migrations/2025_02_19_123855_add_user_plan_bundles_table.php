<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_plan_bundles', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->integer('remaining');

            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('plan_id');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');

            $table->uuid('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_plan_bundles');
    }
};
