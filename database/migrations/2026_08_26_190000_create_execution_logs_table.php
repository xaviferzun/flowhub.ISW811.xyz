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
        //Guarda el resultado de cada ejecucion de una automatizacion: si tuvo exito o fallo,
        //con que datos arranco, que resultado dio, y el detalle del error si algo salio mal.
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('execution_id')->index();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->json('input_data')->nullable();
            $table->json('result')->nullable();
            $table->text('error_detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_logs');
    }
};
