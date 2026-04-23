<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('instagram_url')->nullable()->after('cover_image');
            $table->date('date')->nullable()->after('instagram_url');
            $table->string('location')->nullable()->after('date');
            $table->boolean('is_featured')->default(false)->after('location');
            $table->string('status')->default('published')->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'date', 'location', 'is_featured', 'status']);
        });
    }
};
