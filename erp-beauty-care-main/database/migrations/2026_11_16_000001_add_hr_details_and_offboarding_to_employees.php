<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'birthdate')) {
                $table->string('birthdate')->nullable();
            }
            if (!Schema::hasColumn('employees', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('employees', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('employees', 'termination_reason')) {
                $table->text('termination_reason')->nullable();
            }
            if (!Schema::hasColumn('employees', 'termination_notice_type')) {
                $table->string('termination_notice_type')->nullable();
            }
            if (!Schema::hasColumn('employees', 'severance_amount')) {
                $table->decimal('severance_amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('employees', 'terminated_at')) {
                $table->timestamp('terminated_at')->nullable();
            }
            if (!Schema::hasColumn('employees', 'offboarding_checklist')) {
                $table->text('offboarding_checklist')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $cols = ['birthdate', 'phone', 'address', 'termination_reason', 'termination_notice_type', 'severance_amount', 'terminated_at', 'offboarding_checklist'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
