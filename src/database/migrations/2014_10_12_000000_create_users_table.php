<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->string('name'); // 名前
            $table->string('email')->unique(); // メールアドレス（ユニーク）
            $table->string('password'); // パスワード
            $table->string('profile_image')->nullable(); // プロフィール画像（nullable）
            $table->string('profile_name')->nullable(); // プロフィール名（nullable）
            $table->timestamps(); // created_at と updated_at
            $table->timestamp('email_verified_at')->nullable(); // メール確認日時
            $table->string('remember_token')->nullable(); // パスワードリセット用トークン
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
