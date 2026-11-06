<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cross-division access granted by Admin/Owner to a division team (role)
        Schema::create('access_grants', function (Blueprint $table) {
            $table->id();
            $table->string('grantee_role');   // e.g. Warehouse
            $table->string('app_key');        // e.g. accounting
            $table->string('granted_by');     // actor name
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['grantee_role', 'app_key']);
        });

        // Team inbox notifications (addressed to a division/role)
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_role');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['recipient_role', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('access_grants');
    }
};

