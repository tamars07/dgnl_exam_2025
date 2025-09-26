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
        Schema::create('examiner_rubric_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examiner_id')->comment('giam khao');
            $table->uuid('examinee_test_uuid')->comment('mã định danh bài làm thí sinh');
            $table->uuid('examinee_answer_uuid')->comment('mã định danh bài làm thí sinh');
            $table->string('examinee_test_code',50)->nullable()->comment('mã phách - khởi tạo khi bắt đầu chấm thi');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('question_id');
            $table->tinyInteger('matrix_location')->default(0)->comment('Vị trí xuất hiện trong ma trận');
            $table->unsignedBigInteger('question_type_id')->nullable();
            $table->unsignedBigInteger('rubric_id')->nullable();
            $table->unsignedBigInteger('rubric_criteria_id')->nullable();
            $table->double('score')->default(0);
            $table->timestamps();

            //indexing
            $table->index(['rubric_criteria_id', 'examiner_id','examinee_test_code'],'index_rubric_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiner_rubric_details');
    }
};
