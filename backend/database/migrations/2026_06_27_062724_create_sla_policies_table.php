<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('priority', ['low','medium','high','urgent']);
            $table->integer('response_hours');
            $table->integer('resolution_hours');
            $table->timestamps();
            $table->unique(['organization_id','priority']);
        });
    }
    public function down(): void { Schema::dropIfExists('sla_policies'); }
};
