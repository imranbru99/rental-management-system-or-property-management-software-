<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('role', 32)->default('tenant')->index()->after('avatar_path');
            $table->string('status', 32)->default('active')->index()->after('role');
            $table->string('locale', 8)->default('en')->after('status');
            $table->string('timezone')->default('Asia/Dhaka')->after('locale');
            $table->date('date_of_birth')->nullable()->after('timezone');
            $table->string('national_id')->nullable()->after('date_of_birth');
            $table->timestamp('last_login_at')->nullable()->after('national_id');
            $table->foreignId('current_organization_id')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar_path', 'role', 'status', 'locale', 'timezone',
                'date_of_birth', 'national_id', 'last_login_at', 'current_organization_id',
            ]);
        });
    }
};
