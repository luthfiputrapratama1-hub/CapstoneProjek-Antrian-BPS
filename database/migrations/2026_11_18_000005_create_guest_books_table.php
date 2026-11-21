<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('guest_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->unique()->constrained('queues')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone_number', 25);
            $table->string('email')->nullable();
            $table->string('institution_name');
            $table->string('institution_type', 50)->default('umum');
            $table->text('data_needs_description');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('guest_books');
    }
};
