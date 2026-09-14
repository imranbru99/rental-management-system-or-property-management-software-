<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('category')->default('general');
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 32)->default('apartment');
            $table->string('status', 32)->default('draft')->index();
            $table->string('address_line');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 8)->default('BD');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedInteger('square_feet')->nullable();
            $table->year('year_built')->nullable();
            $table->unsignedInteger('base_rent')->default(0);
            $table->unsignedInteger('security_deposit')->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->boolean('utilities_included')->default(false);
            $table->boolean('pet_friendly')->default(false);
            $table->boolean('furnished')->default(false);
            $table->json('amenities')->nullable();
            $table->longText('description')->nullable();
            $table->longText('house_rules')->nullable();
            $table->string('cover_photo_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('floor_plan_path')->nullable();
            $table->json('photos')->nullable();
            $table->json('pricing_rules')->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'slug']);
            $table->index(['city', 'status']);
        });

        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status', 32)->default('vacant')->index();
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedInteger('square_feet')->nullable();
            $table->unsignedInteger('rent')->default(0);
            $table->unsignedInteger('security_deposit')->default(0);
            $table->json('amenities')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['property_id', 'slug']);
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedInteger('rent')->default(0);
            $table->unsignedInteger('security_deposit')->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->date('available_from')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->text('moderation_notes')->nullable();
            $table->boolean('ai_generated')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('unlisted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'slug']);
        });

        Schema::create('calendar_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('reason')->nullable();
            $table->string('source')->default('manual');
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'listing_id']);
        });

        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('criteria');
            $table->string('alert_frequency')->default('daily');
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('viewings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 16)->default('in_person');
            $table->string('status', 32)->default('requested');
            $table->timestamp('scheduled_at');
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewings');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('calendar_blocks');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('property_units');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('amenities');
    }
};
