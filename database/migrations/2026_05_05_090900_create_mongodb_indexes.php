<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production-grade MongoDB indexes for HRMS performance engine.
     *
     * Compound indexes on the fields used in the most frequent query patterns:
     *  - Leaderboard: { month, rank }
     *  - Score lookup: { employee_id, month }
     *  - Rating queries: { employee_id, month } and { evaluator_id, employee_id }
     *  - Attendance: { employee_id, date }
     */
    public function up(): void
    {
        // ── performance_records ────────────────────────────────────────
        Schema::connection('mongodb')->table('performance_records', function (Blueprint $table) {
            // Primary leaderboard query: filter by month, sort by rank
            $table->index(['month' => 1, 'rank' => 1]);

            // Per-employee record lookup (UNIQUE to prevent duplicates)
            $table->unique(['employee_id' => 1, 'month' => 1]);

            // Score sorting within a month (admin dashboard top performers)
            $table->index(['month' => 1, 'final_score' => -1]);
        });

        // ── ratings ───────────────────────────────────────────────────
        Schema::connection('mongodb')->table('ratings', function (Blueprint $table) {
            // Most common: fetch all ratings for an employee in a month
            $table->index(['employee_id' => 1, 'month' => 1]);

            // Cooldown check: evaluator + employee + created_at
            $table->index(['evaluator_id' => 1, 'employee_id' => 1]);
        });

        // ── attendances ───────────────────────────────────────────────
        Schema::connection('mongodb')->table('attendances', function (Blueprint $table) {
            // Range queries: employee + date range for month calculation
            $table->index(['employee_id' => 1, 'date' => 1]);
        });

        // ── monthly_rewards ───────────────────────────────────────────
        Schema::connection('mongodb')->table('monthly_rewards', function (Blueprint $table) {
            $table->index(['employee_id' => 1, 'month' => 1]);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('performance_records', function (Blueprint $table) {
            $table->dropIndex(['month', 'rank']);
            $table->dropIndex(['employee_id', 'month']);
            $table->dropIndex(['month', 'final_score']);
        });

        Schema::connection('mongodb')->table('ratings', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'month']);
            $table->dropIndex(['evaluator_id', 'employee_id']);
        });

        Schema::connection('mongodb')->table('attendances', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date']);
        });

        Schema::connection('mongodb')->table('monthly_rewards', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'month']);
        });
    }
};
