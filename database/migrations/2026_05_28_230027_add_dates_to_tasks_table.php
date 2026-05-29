<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('duedate')->nullable()->after('position');
            $table->date('plan_start')->nullable()->after('duedate');
            $table->date('plan_end')->nullable()->after('plan_start');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['duedate', 'plan_start', 'plan_end']);
        });
    }
};
