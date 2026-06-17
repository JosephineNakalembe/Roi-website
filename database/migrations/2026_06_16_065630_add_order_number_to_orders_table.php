<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
        });

        // Generate order_numbers for existing orders based on their ID
        $orders = DB::table('orders')->orderBy('id')->get();
        $counter = 0;
        foreach ($orders as $order) {
            $counter++;
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_number' => 'RS24' . str_pad($counter, 3, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};