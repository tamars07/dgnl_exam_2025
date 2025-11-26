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
        Schema::create('test_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            // $table->text('desc')->nullable();
            $table->unsignedInteger('time')->default(0)->comment('thời gian làm bài');
            $table->unsignedInteger('no_questions')->default(0)->comment('số lượng câu hỏi');
            $table->unsignedInteger('no_parts')->default(0)->comment('số phần thi');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('test_forms', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_forms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_forms');
    }
};
