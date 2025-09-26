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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('log_uuid')->unique()->comment('mã định danh log');
            $table->string('username')->comment('Tài khoản thực hiện thao tác');
            $table->unsignedBigInteger('role_id')->comment('Vai trò');
            $table->string('council_code',50)->nullable();
            $table->string('council_turn_code',50)->nullable();
            $table->string('room_code',50)->nullable();
            $table->text('action')->nullable()->comment('thao tác: login, logout, submit answer, start test, submit test, adding time, recover');
            $table->longText('desc')->nullable()->comment('diễn giải thao tác');
            $table->unsignedBigInteger('log_time')->comment('Thời điểm lưu dữ liệu');
            $table->timestamps();
            
            //indexing
            $table->index(['username', 'council_code','council_turn_code'],'user_logs');
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('activity_logs');
    }
};
