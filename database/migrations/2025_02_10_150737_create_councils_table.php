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
        Schema::create('councils', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->unique()->comment('mã hội đồng thi');
            $table->string('desc',255)->nullable();
            $table->unsignedInteger('no_turns')->default(0)->comment('số ca thi');
            $table->date('start_at')->comment('ngay bat dau');
            $table->date('finish_at')->comment('ngay ket thuc');
            $table->boolean('is_autostart')->default(false)->comment('1: khoi tao tu dong, 0: khoi tao thu cong');
            $table->unsignedInteger('import_testdata_before_time')->default(60)->comment('số phút ca thi được phép nhập đề trước giờ thi');
            $table->boolean('is_backup')->default(false)->comment('1: đã backup, 0: chưa backup');
            $table->boolean('is_clear')->default(false)->comment('1: đã xoá data, 0: chưa xoá data');
            $table->boolean('status')->default(true);
            $table->string('organization_code',50);
            $table->foreign('organization_code')->references('code')->on('organizations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('monitor_id')->nullable()->comment('điểm trưởng HĐ thi');
            $table->foreign('monitor_id')->references('id')->on('monitors')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::table('councils', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('councils', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('councils');
    }
};
