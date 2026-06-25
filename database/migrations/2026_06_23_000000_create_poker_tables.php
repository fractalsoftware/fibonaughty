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
        // 1. Rooms Table (utilizing NanoID PK)
        Schema::create('rooms', function (Blueprint $table) {
            $table->string('id', 21)->primary(); // NanoID primary key
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('deck_type', ['fibonacci', 'tshirt'])->default('fibonacci');
            $table->enum('status', ['idle', 'voting', 'revealed'])->default('idle');
            $table->string('current_task_title')->nullable();
            $table->timestamps();
        });

        // 2. Participants Table (utilizing UUID PK)
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv4 guest token primary key
            $table->string('room_id', 21);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->index(['room_id', 'is_active']);
        });

        // 3. Votes Table
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('room_id', 21);
            $table->uuid('participant_id');
            $table->string('task_title'); // Isolates votes per story round title
            $table->string('estimate_value'); // '5', 'S', '☕', etc.
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            
            // Ensures single vote limit per participant per story round
            $table->unique(['room_id', 'participant_id', 'task_title']);
        });

        // 4. Estimation History Table
        Schema::create('estimation_history', function (Blueprint $table) {
            $table->id();
            $table->string('room_id', 21);
            $table->string('task_title');
            $table->string('deck_type');
            $table->string('final_estimate')->nullable(); // Final consensus value
            $table->boolean('consensus_reached')->default(false);
            $table->integer('rounds_count')->default(1);
            $table->timestamp('completed_at')->useCurrent();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimation_history');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('rooms');
    }
};
