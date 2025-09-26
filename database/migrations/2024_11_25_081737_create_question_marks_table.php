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
        Schema::create('question_marks', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->text('desc')->nullable();
            $table->double('value')->default(1.0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('question_marks', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_marks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('question_marks');
    }
};
