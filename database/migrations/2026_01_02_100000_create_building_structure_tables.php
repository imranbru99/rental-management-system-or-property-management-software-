<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedSmallInteger('floor_count')->default(0)->after('featured_until');
            $table->unsignedSmallInteger('units_per_floor')->default(0)->after('floor_count');
            $table->unsignedTinyInteger('default_rooms')->default(3)->after('units_per_floor');
            $table->unsignedTinyInteger('default_baths')->default(3)->after('default_rooms');
            $table->unsignedTinyInteger('default_balconies')->default(1)->after('default_baths');
            $table->unsignedInteger('default_unit_rent')->default(0)->after('default_balconies');
            $table->unsignedInteger('default_unit_deposit')->default(0)->after('default_unit_rent');
        });

        Schema::create('property_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('level')->default(1);
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['property_id', 'sort_order']);
        });

        Schema::table('property_units', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->after('property_id')->constrained('property_floors')->cascadeOnDelete();
            $table->string('code')->nullable()->after('name');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('code');
            $table->unsignedTinyInteger('balconies')->default(0)->after('bathrooms');
            $table->foreignId('tenant_id')->nullable()->after('amenities')->constrained('users')->nullOnDelete();
        });

        Schema::create('unit_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('property_units')->cascadeOnDelete();
            $table->string('type', 32)->default('room');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['unit_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_spaces');

        Schema::table('property_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('floor_id');
            $table->dropColumn(['code', 'sort_order', 'balconies']);
        });

        Schema::dropIfExists('property_floors');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'floor_count', 'units_per_floor', 'default_rooms', 'default_baths',
                'default_balconies', 'default_unit_rent', 'default_unit_deposit',
            ]);
        });
    }
};
