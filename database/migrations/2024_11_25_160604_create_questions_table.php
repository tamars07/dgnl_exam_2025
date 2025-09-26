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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cloze_id')->nullable()->comment('quan hệ cho câu hỏi ngữ cảnh');
            // $table->foreign('cloze_id')->references('id')->on('questions')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('question_type_id')->nullable();
            $table->foreign('question_type_id')->references('id')->on('question_types')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');

            $table->uuid('uuid');
            $table->string('code',30);
            $table->longText('pre_content')->nullable();
            $table->longText('content')->nullable();
            $table->longText('post_content')->nullable();
            $table->text('option_1')->nullable();
            $table->text('option_2')->nullable();
            $table->text('option_3')->nullable();
            $table->text('option_4')->nullable();
            $table->text('option_5')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->text('option_6')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->text('option_7')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->text('option_8')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->text('option_9')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->text('option_10')->nullable()->comment('sử dụng cho các câu hỏi có nhiều hơn 4 phương án');
            $table->unsignedInteger('min_words')->default(0)->comment('Số chữ tối thiểu dành cho bài luận, 0 là không giới hạn');
            $table->unsignedInteger('max_words')->default(0)->comment('Số chữ tối đa dành cho bài luận, 0 là không giới hạn');
            $table->boolean('is_shuffled')->default(false)->comment('câu hỏi có hoán đổi đáp án');
            $table->string('answer_type',20)->default(1)->comment('Kiểu đáp án để ràng buộc cách chấm');
            $table->string('answer_key',255)->nullable()->comment('Phương án trả lời đúng');
            $table->tinyInteger('matrix_location')->default(0)->comment('Vị trí xuất hiện trong ma trận');
            $table->tinyInteger('context_order')->nullable()->default(0)->comment('Vị trí xuất hiện của câu hỏi trong câu hỏi ngữ cảnh');
            $table->tinyInteger('no_options')->default(0)->comment('Số lượng phương án trả lời');
            $table->tinyInteger('no_used')->default(0)->comment('Số lần sử dụng');
            $table->unsignedBigInteger('last_used')->default(0)->comment('Thời điểm sử dụng gần nhất');

            $table->unsignedBigInteger('competency_id')->nullable();
            $table->foreign('competency_id')->references('id')->on('competencies')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('taxonomy_id')->nullable();
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->foreign('topic_id')->references('id')->on('topics')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('difficult_id')->nullable();
            $table->foreign('difficult_id')->references('id')->on('difficults')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('question_store_id')->nullable();
            $table->foreign('question_store_id')->references('id')->on('question_stores')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('question_mark_id')->default(1);
            $table->foreign('question_mark_id')->references('id')->on('question_marks')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable()->comment('người tạo câu hỏi');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('questions');
    }
};
