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
        Schema::create('examinee_answers', function (Blueprint $table) {
            $table->id();
            $table->uuid('examinee_test_uuid')->nullable()->comment('mã định danh cặp thí sinh và đề thi');
            $table->uuid('examinee_answer_uuid')->unique()->comment('mã định danh bài làm thí sinh');
            $table->string('examinee_code',50)->comment('SBD');
            $table->string('examinee_account',50)->comment('Tài khoản dự thi');
            $table->unsignedBigInteger('test_mix_id');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            // $table->foreign('question_id')->references('id')->on('questions')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('question_type_id');
            $table->unsignedTinyInteger('question_number')->default(0)->comment('Thứ tự câu hỏi trong đề thi');
            $table->longText('answer_detail')->nullable()->comment('Nội dung trả lời');
            $table->unsignedBigInteger('submitting_time')->default(0)->comment('Thời điểm lưu dữ liệu trả lời');
            $table->unsignedInteger('remaining_time')->default(0)->comment('Thời gian làm bài còn lại tính theo giây');
            $table->timestamps();

            //indexing
            $table->index(['examinee_answer_uuid', 'examinee_test_uuid','test_mix_id'],'index_answers_1');
            $table->index(['examinee_account', 'question_id','test_mix_id'],'index_answers_2');
        });
        Schema::table('examinee_answers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinee_answers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('examinee_answers');
    }
};
