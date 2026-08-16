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
        //FH-25 Disparador de una automatización (relación 1:1). type + config permiten soportar cualquier proveedor sin crear una tabla nueva por cada tipo de trigger.
        Schema::create('triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};
