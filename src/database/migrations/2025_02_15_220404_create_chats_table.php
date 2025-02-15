<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id'); // 取引ID
            $table->unsignedBigInteger('user_id'); // 送信者ID
            $table->string('message', 400)->nullable(); // メッセージ（400文字以内）
            $table->string('image_path')->nullable(); // 画像パス
            $table->boolean('is_deleted')->default(0); // 削除フラグ
            $table->boolean('is_edited')->default(0); // 編集フラグ
            $table->boolean('is_read')->default(0); // 既読フラグ
            $table->timestamps();

            // 外部キー制約
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
