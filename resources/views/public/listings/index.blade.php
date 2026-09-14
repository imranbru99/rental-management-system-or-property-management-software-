@extends('layouts.public')

@section('title', 'Browse rentals')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-3xl font-semibold">Available homes</h1>
    <form class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-6">
        <input name="q" value="{{ request('q') }}" placeholder="Location or keywords" class="rounded-xl border border-slate-200 px-3 py-2 md:col-span-2">
        <input name="min_bedrooms" type="number" min="0" value="{{ request('min_bedrooms') }}" placeholder="Min beds" class="rounded-xl border border-slate-200 px-3 py-2">
        <input name="max_rent" type="number" min="0" value="{{ request('max_rent') }}" placeholder="Max rent (minor units)" class="rounded-xl border border-slate-200 px-3 py-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="pets" value="1" @checked(request('pets'))> Pet-friendly</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="furnished" value="1" @checked(request('furnished'))> Furnished</label>
        <div class="flex gap-2 md:col-span-6">
            <button class="rounded-xl bg-teal-700 px-4 py-2 font-medium text-white">Filter</button>
            <button form="save-search" class="rounded-xl border border-teal-700 px-4 py-2 font-medium text-teal-800">Save this search</button>
        </div>
    </form>
    <form id="save-search" method="POST" action="{{ route('saved-searches.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="min_bedrooms" value="{{ request('min_bedrooms') }}">
        <input type="hidden" name="max_rent" value="{{ request('max_rent') }}">
        @if (request('pets')) <input type="hidden" name="pets" value="1"> @endif
        @if (request('furnished')) <input type="hidden" name="furnished" value="1"> @endif
    </form>

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        @forelse ($listings as $listing)
            @include('public.partials.listing-card', ['listing' => $listing])
        @empty
            <p class="text-slate-500">No listings match those filters.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $listings->links() }}</div>
</div>
@endsection
