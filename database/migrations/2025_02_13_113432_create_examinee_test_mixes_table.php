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
        Schema::create('examinee_test_mixes', function (Blueprint $table) {
            $table->id();
            $table->uuid('examinee_test_uuid')->unique()->comment('mã định danh cặp thí sinh và đề thi');
            $table->string('examinee_test_code',50)->nullable()->comment('mã phách - khởi tạo khi bắt đầu chấm thi');
            $table->string('examinee_code',50)->comment('SBD');
            $table->string('examinee_account',50)->comment('Tài khoản dự thi');
            $table->unsignedBigInteger('test_mix_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('council_code',50);
            $table->string('council_turn_code',50);
            $table->string('room_code',50);
            $table->unsignedBigInteger('start_time')->default(0)->comment('Thời điểm bắt đầu làm bài');
            $table->unsignedBigInteger('expected_finish_time')->default(0)->comment('Thời điểm nộp bài dự kiến');
            $table->unsignedBigInteger('finish_time')->default(0)->comment('Thời điểm nộp bài thực tế');
            $table->unsignedInteger('remaining_time')->default(0)->comment('Thời gian làm bài còn lại tính theo giây');
            $table->unsignedInteger('bonus_time')->default(0)->comment('Thời gian cộng thêm cho thí sinh tính theo giây');
            $table->json('answer_logs')->nullable()->comment('Dữ liệu toàn bộ bài làm của thí sinh');
            $table->timestamps();

            //indexing
            $table->index(['examinee_code','examinee_account','council_code','council_turn_code'],'index_test_mixes');
        });
        Schema::table('examinee_test_mixes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinee_test_mixes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('examinee_test_mixes');
    }
};
