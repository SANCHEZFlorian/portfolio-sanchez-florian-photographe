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
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('delivery_enabled')->default(false)->after('status');
            $table->string('delivery_token', 64)->nullable()->unique()->after('delivery_enabled');
            $table->string('delivery_title')->nullable()->after('delivery_token');
            $table->text('delivery_message')->nullable()->after('delivery_title');
            $table->unsignedInteger('delivery_download_count')->default(0)->after('delivery_message');
            $table->dateTime('delivery_expires_at')->nullable()->after('delivery_download_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_enabled',
                'delivery_token',
                'delivery_title',
                'delivery_message',
                'delivery_download_count',
                'delivery_expires_at',
            ]);
        });
    }
};
