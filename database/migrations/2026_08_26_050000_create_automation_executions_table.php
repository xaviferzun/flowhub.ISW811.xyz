<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Registra que un disparo especifico de una automatizacion ya fue procesado,
        //para no repetir las acciones si el mismo trabajo se reprocesa.
        Schema::create('automation_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('execution_id')->unique();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_executions');
    }
};
