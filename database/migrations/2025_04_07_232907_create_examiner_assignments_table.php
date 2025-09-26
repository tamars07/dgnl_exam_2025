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
        Schema::create('examiner_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('examinee_test_uuid')->comment('mã định danh bài làm thí sinh');
            $table->uuid('examinee_answer_uuid')->comment('mã định danh bài làm thí sinh');
            $table->string('examinee_test_code',50)->nullable()->comment('mã phách - khởi tạo khi bắt đầu chấm thi');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('examiner_id')->comment('giam khao');
            $table->unsignedBigInteger('examiner_pair_id')->comment('cap giam khao');
            $table->unsignedBigInteger('answer_key_id')->comment('chi tiet bai lam thi sinh');
            $table->date('start_at')->nullable()->comment('ngay bat dau');
            $table->date('finish_at')->nullable()->comment('ngay ket thuc');
            $table->boolean('is_done')->default(false)->comment('đã chấm xong');
            $table->boolean('is_review')->default(false)->comment('chấm phúc khảo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiner_assignments');
    }
};
