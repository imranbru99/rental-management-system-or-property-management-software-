@extends('layouts.public')

@section('title', $listing->title)

@section('content')
<div class="mx-auto max-w-5xl px-4 py-10">
    <p class="text-sm text-teal-700">{{ $listing->property?->full_address }}</p>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
        <h1 class="text-4xl font-semibold">{{ $listing->title }}</h1>
        <form method="POST" action="{{ route('listings.favorite', $listing) }}">
            @csrf
            <button class="rounded-full border border-teal-700 px-4 py-2 text-sm font-medium text-teal-800">
                {{ $isFavorite ? 'Saved' : 'Save favorite' }}
            </button>
        </form>
    </div>
    <p class="mt-4 text-2xl font-semibold">{{ \App\Support\Money::format($listing->rent, $listing->currency) }} <span class="text-base font-normal text-slate-500">/ month</span></p>
    <div class="mt-6 h-72 rounded-3xl bg-gradient-to-br from-teal-100 to-slate-200"></div>
    <article class="prose mt-8 max-w-none text-slate-700">{!! nl2br(e($listing->description)) !!}</article>
    <dl class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm md:grid-cols-4">
        <div><dt class="text-xs uppercase text-slate-500">Beds</dt><dd class="text-lg font-medium">{{ $listing->property?->bedrooms }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Baths</dt><dd class="text-lg font-medium">{{ $listing->property?->bathrooms }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Deposit</dt><dd class="text-lg font-medium">{{ \App\Support\Money::format($listing->security_deposit, $listing->currency) }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Available</dt><dd class="text-lg font-medium">{{ $listing->available_from?->toFormattedDateString() ?? 'Now' }}</dd></div>
    </dl>
    @if (filled($listing->property?->amenities))
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($listing->property->amenities as $amenity)
                <span class="rounded-full bg-teal-50 px-3 py-1 text-sm text-teal-800">{{ $amenity }}</span>
            @endforeach
        </div>
    @endif

    <div class="mt-10 grid gap-8 md:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Apply</h2>
            @guest
                <a href="{{ url('/tenant/login') }}" class="mt-4 inline-flex rounded-full bg-teal-700 px-6 py-3 font-medium text-white">Sign in to apply</a>
            @else
                <form method="POST" action="{{ route('listings.apply', $listing) }}" class="mt-4 grid gap-3">
                    @csrf
                    <input name="monthly_income" type="number" min="0" placeholder="Monthly income (minor units)" class="rounded-xl border border-slate-200 px-3 py-2">
                    <input name="employer" placeholder="Employer" class="rounded-xl border border-slate-200 px-3 py-2">
                    <input name="occupants" type="number" min="1" value="1" required class="rounded-xl border border-slate-200 px-3 py-2">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="has_pets" value="1"> I have pets</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="consent_background_check" value="1" required> I consent to a background check</label>
                    <button class="rounded-full bg-teal-700 px-6 py-3 font-medium text-white">Submit application</button>
                </form>
            @endguest
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Book a viewing</h2>
            @guest
                <a href="{{ url('/tenant/login') }}" class="mt-4 inline-flex rounded-full border border-teal-700 px-6 py-3 font-medium text-teal-800">Sign in to request a tour</a>
            @else
                <form method="POST" action="{{ route('listings.viewings.store', $listing) }}" class="mt-4 grid gap-3">
                    @csrf
                    <input name="scheduled_at" type="datetime-local" required class="rounded-xl border border-slate-200 px-3 py-2">
                    <select name="type" class="rounded-xl border border-slate-200 px-3 py-2">
                        <option value="in_person">In person</option>
                        <option value="video">Video tour</option>
                    </select>
                    <textarea name="notes" placeholder="Anything the host should know?" class="rounded-xl border border-slate-200 px-3 py-2"></textarea>
                    <button class="rounded-full border border-teal-700 px-6 py-3 font-medium text-teal-800">Request viewing</button>
                </form>
            @endguest
        </section>
    </div>
</div>
@endsection
