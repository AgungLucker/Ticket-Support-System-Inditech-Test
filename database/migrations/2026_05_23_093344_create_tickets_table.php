<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('Open')->index(); // Open, Assigned, In Progress, Waiting for Customer, Resolved, Closed, Reopened, Escalated
            
            $table->foreignId('priority_id')->constrained('priorities')->restrictOnDelete()->index();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete()->index();
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete()->index();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete()->index();
            
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            // Index for created_at and updated_at
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
