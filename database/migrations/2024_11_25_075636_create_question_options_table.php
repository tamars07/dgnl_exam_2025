<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->tinyInteger('order')->default(0)->comment('thứ tự hiển thị, 0 là random');
            $table->boolean('is_correct')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('question_options', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('question_options');
    }
};
