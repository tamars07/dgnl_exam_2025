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
        Schema::create('examinees', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->comment('số báo danh thí sinh');
            $table->string('id_card_number')->comment('CCCD');
            $table->string('lastname')->comment('Họ lót');
            $table->string('firstname')->comment('Tên');
            $table->string('birthday',10)->nullable()->comment('Ngày tháng năm sinh DD/MM/YYYY');
            $table->longText('avatar')->nullable()->comment('Ảnh thí sinh');
            $table->string('password');
            $table->unsignedSmallInteger('seat_number')->default(0)->comment('Vị trí ngồi trong phòng');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
            $table->string('council_code',50);
            $table->foreign('council_code')->references('code')->on('councils')->onUpdate('cascade')->onDelete('cascade');
            $table->string('council_turn_code',50);
            $table->foreign('council_turn_code')->references('code')->on('council_turns')->onUpdate('cascade')->onDelete('cascade');
            $table->string('room_code',50);
            $table->foreign('room_code')->references('code')->on('rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id')->default(8)->comment('Vai trò là thí sinh');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('is_backup')->default(0)->comment('tài khoản dự phòng');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::table('examinees', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('examinees');
    }
};
