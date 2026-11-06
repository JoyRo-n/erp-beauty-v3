<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requester_role');   // e.g. Sales, Warehouse, Finance, Purchasing, Studio
            $table->string('requester_name');   // e.g. Kevin Mahendra
            $table->string('app_key');          // e.g. inventory, accounting
            $table->text('reason');
            $table->string('status', 20)->default('Pending'); // Pending, Approved, Rejected
            $table->string('reviewed_by')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['status', 'created_at']);
            $table->index(['requester_role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};

