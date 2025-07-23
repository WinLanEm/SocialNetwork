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
        Schema::create('chat_unread_messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->integer('unread_count')->default(0);
            $table->timestamps();
            $table->unique(['chat_id', 'recipient_id', 'sender_id']);
            $table->index(['sender_id','recipient_id', 'unread_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_unread_messages');
    }
};
