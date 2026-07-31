<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_search_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_id', 255)->nullable()->index();
            $table->string('query', 255);
            $table->integer('results_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_id', 255)->nullable()->index();
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::table('user_search_logs', function (Blueprint $table) {
            $table->index(['created_at']);
        });

        Schema::table('product_views', function (Blueprint $table) {
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('user_search_logs');
    }
};
