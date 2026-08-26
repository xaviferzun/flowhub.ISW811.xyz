<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //FH-51 Como se combina esta condicion con la anterior en la secuencia (and/or). Default 'and' preserva el comportamiento previo (todas deben cumplirse)
        Schema::table('conditions', function (Blueprint $table) {
            $table->string('logic')->default('and')->after('automation_id');
        });
    }

    public function down(): void
    {
        Schema::table('conditions', function (Blueprint $table) {
            $table->dropColumn('logic');
        });
    }
};