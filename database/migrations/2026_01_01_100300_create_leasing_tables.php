<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('jurisdiction', 16)->default('BD');
            $table->longText('body');
            $table->json('clauses')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('submitted')->index();
            $table->unsignedInteger('monthly_income')->nullable();
            $table->string('employer')->nullable();
            $table->string('employment_status')->nullable();
            $table->unsignedTinyInteger('occupants')->default(1);
            $table->boolean('has_pets')->default(false);
            $table->boolean('consent_background_check')->default(false);
            $table->json('references')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->text('risk_notes')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('verification_status', 32)->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('lease_templates')->nullOnDelete();
            $table->foreignId('primary_tenant_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('draft')->index();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedInteger('rent');
            $table->unsignedInteger('security_deposit')->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->unsignedTinyInteger('late_fee_grace_days')->default(5);
            $table->unsignedTinyInteger('late_fee_percent')->default(5);
            $table->unsignedTinyInteger('notice_days')->default(30);
            $table->unsignedTinyInteger('renewal_offer_days')->default(60);
            $table->boolean('auto_renew')->default(false);
            $table->unsignedTinyInteger('escalation_percent')->default(0);
            $table->longText('terms')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lease_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('rent_share')->nullable();
            $table->string('role', 32)->default('tenant');
            $table->timestamps();
            $table->unique(['lease_id', 'user_id']);
        });

        Schema::create('lease_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('tenant');
            $table->string('signature_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->date('effective_on');
            $table->text('body')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('credit_amount')->default(0);
            $table->string('status', 32)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('lease_signatures');
        Schema::dropIfExists('lease_tenants');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('lease_templates');
    }
};
