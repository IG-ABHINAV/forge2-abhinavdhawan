<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['open','in_progress','resolved','closed'])->default('open');
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};
