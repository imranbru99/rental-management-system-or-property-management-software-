<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedTinyInteger('default_kitchens')->default(1)->after('default_balconies');
            $table->unsignedTinyInteger('default_dining')->default(1)->after('default_kitchens');
        });

        Schema::table('property_units', function (Blueprint $table) {
            $table->unsignedTinyInteger('kitchens')->default(0)->after('balconies');
            $table->unsignedTinyInteger('dining')->default(0)->after('kitchens');
        });
    }

    public function down(): void
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->dropColumn(['kitchens', 'dining']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['default_kitchens', 'default_dining']);
        });
    }
};
