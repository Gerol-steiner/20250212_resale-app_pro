<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade'); // 評価するユーザー
            $table->foreignId('rated_id')->constrained('users')->onDelete('cascade'); // 評価されるユーザー
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade'); // 取引ID
            $table->tinyInteger('rating')->unsigned()->comment('0～5の評価'); // 0～5の評価
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
