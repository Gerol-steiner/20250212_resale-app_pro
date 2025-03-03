<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInProgressToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // 取引中かどうかを示すカラムの追加
            $table->boolean('in_progress')->nullable()->default(null)->comment('1: 取引中, 2: 取引完了, null: 未設定');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // 変更を元に戻す
            $table->dropColumn('in_progress');
        });
    }
}
