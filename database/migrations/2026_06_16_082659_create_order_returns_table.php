<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create order_returns table (main return request)
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason'); // Wrong items received, Item Arrived Damaged, Defective/Faulty, Wrong Size
            $table->text('notes');
            $table->string('refund_number');
            $table->string('refund_network'); // Airtel Money, MTN
            $table->string('refund_name');
            $table->string('pickup_address');
            $table->string('pickup_contact');
            $table->string('pickup_area');
            $table->decimal('pickup_fee', 10, 2)->default(0);
            $table->string('images')->nullable(); // comma-separated file paths
            $table->string('status')->default('pending'); // pending, approved, rejected, refunded
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Create return_items table (which items from order are being returned)
        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Create return_status_updates table (timeline for returns)
        Schema::create('order_return_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_updates');
        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');
    }
};