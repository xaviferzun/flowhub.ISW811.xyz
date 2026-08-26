<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //FH-31 Marca de tiempo del ultimo polling exitoso, para no repetir el mismo issue detectado
        Schema::table('triggers', function (Blueprint $table) {
            $table->timestamp('last_checked_at')->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('triggers', function (Blueprint $table) {
            $table->dropColumn('last_checked_at');
        });
    }
};