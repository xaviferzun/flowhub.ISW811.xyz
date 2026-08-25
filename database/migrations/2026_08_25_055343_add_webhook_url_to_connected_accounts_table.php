<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //FH-35 URL de webhook autorizada por Discord (scope webhook.incoming), usada por la accion discord.send_message
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->text('webhook_url')->nullable()->after('refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->dropColumn('webhook_url');
        });
    }
};