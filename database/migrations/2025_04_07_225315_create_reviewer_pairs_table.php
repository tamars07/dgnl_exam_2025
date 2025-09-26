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
        Schema::create('reviewer_pairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('code')->comment('mã cặp chấm');
            $table->unsignedInteger('no_tests')->default(0)->comment('số bài thi sẽ gán');
            $table->date('start_at')->comment('ngay bat dau');
            $table->date('finish_at')->comment('ngay ket thuc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_pairs');
    }
};
