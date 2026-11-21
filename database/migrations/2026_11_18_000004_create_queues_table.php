<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date')->index();
            $table->string('ticket_code', 10);
            $table->integer('ticket_number');
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('waiting')->index();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('wait_duration_seconds')->nullable();
            $table->integer('serve_duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['queue_date', 'service_id', 'ticket_number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('queues');
    }
};
