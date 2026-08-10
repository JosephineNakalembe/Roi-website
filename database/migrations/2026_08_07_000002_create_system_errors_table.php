<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->string('level')->default('error');
            $table->string('exception')->nullable();
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
