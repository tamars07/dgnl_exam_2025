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
        Schema::create('answer_keys', function (Blueprint $table) {
            $table->id();
            $table->uuid('examinee_test_uuid')->comment('mã định danh bài làm thí sinh');
            $table->uuid('examinee_answer_uuid')->comment('mã định danh bài làm thí sinh');
            $table->string('examinee_test_code',50)->nullable()->comment('mã phách - khởi tạo khi bắt đầu chấm thi');
            $table->string('council_code',50);
            $table->string('council_turn_code',50);
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedTinyInteger('matrix_location')->default(0)->comment('Vị trí xuất hiện trong ma trận');
            $table->unsignedBigInteger('question_type_id')->nullable();
            $table->longText('examinee_answer')->nullable()->comment('Nội dung trả lời');
            $table->unsignedBigInteger('submitting_time')->default(0)->comment('Thời điểm lưu dữ liệu trả lời');
            $table->string('answer_type',20)->default(1)->comment('Kiểu đáp án để ràng buộc cách chấm');
            $table->string('answer_key',255)->nullable()->comment('Phương án trả lời đúng');
            $table->unsignedBigInteger('question_mark_id')->default(1);
            $table->boolean('is_correct')->default(false);
            $table->double('score')->default(0);
            $table->unsignedBigInteger('rubric_id')->nullable()->comment('rubric su dung');
            $table->boolean('is_assigned')->default(false)->comment('đã được gán chấm thi');
            // $table->unsignedBigInteger('examiner_pair_id')->comment('cap giam khao');
            $table->timestamps();

            //indexing
            $table->index(['examinee_test_code', 'question_id','matrix_location'],'index_answer_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_keys');
    }
};
