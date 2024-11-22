<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('tree_parent_post_id')->nullable()->default(null);
            $table->foreign('tree_parent_post_id')
                ->references('id')->on('posts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropColumns('posts', 'tree_parent_post_id');
    }
};
