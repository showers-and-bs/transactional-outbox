<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outgoing_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('event_id');
            $table->string('event');
            $table->text('payload');
            $table->timestamp('success_at')->nullable();
            $table->tinyInteger('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_messages');
    }
};
