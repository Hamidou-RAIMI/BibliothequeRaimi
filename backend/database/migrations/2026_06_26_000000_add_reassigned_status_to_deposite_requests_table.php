<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposite_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'assigned',
                'reassigned',
                'approved_by_manager',
                'rejected_by_manager',
                'second_review',
                'approved',
                'rejected',
                'published'
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('deposite_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved_by_manager',
                'rejected_by_manager',
                'second_review',
                'approved',
                'rejected',
                'published'
            ])->default('pending')->change();
        });
    }
};
