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
        Schema::create('council_turns', function (Blueprint $table) {
            // $table->id();
            $table->string('code',50)->primary()->unique()->comment('mã ca thi');
            $table->string('name',100)->comment('tên ca thi');
            $table->dateTime('start_at')->comment('ngày giờ bắt đầu');
            $table->unsignedInteger('no_rooms')->default(0)->comment('số phòng thi');
            $table->boolean('is_active')->default(false)->comment('1: da khoi tao ca thi, 2: chua khoi tao ca thi');
            $table->boolean('is_backup')->default(false)->comment('1: đã backup, 0: chưa backup');
            $table->string('bk_file_name',255)->nullable()->comment('tên file backup');
            $table->boolean('status')->default(true);
            $table->string('council_code',50);
            $table->foreign('council_code')->references('code')->on('councils')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::table('council_turns', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('council_turns', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('council_turns');
    }
};
