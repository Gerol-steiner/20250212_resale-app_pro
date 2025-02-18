<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('buyer_rated')->default(false)->after('in_progress');
            $table->boolean('seller_rated')->default(false)->after('buyer_rated');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['buyer_rated', 'seller_rated']);
        });
    }
};

