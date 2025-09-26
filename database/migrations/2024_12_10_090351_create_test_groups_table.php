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
        Schema::create('test_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            // $table->string('name')->nullable();
            $table->text('desc')->nullable();
            $table->unsignedTinyInteger('no_subjects')->default(1)->comment('Số môn thi trong bộ đề');
            $table->string('password')->default('ABCDEF123456')->comment('mật khẩu mở đề');
            $table->boolean('is_used')->default(false);
            $table->string('packaged_by')->nullable()->comment('tài khoản người xuất đề');
            $table->bigInteger('packaged_at')->default(0)->comment('thời điểm xuất đề');
            $table->text('encryption_file')->nullable()->comment('file đề thi mã hoá');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('test_groups', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_groups', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_groups');
    }
};
