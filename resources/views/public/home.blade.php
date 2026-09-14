@extends('layouts.public')

@section('title', 'Modern rental management')

@section('content')
<section class="bg-gradient-to-br from-teal-800 to-slate-900 text-white">
    <div class="mx-auto max-w-6xl px-4 py-20">
        <p class="text-sm uppercase tracking-[0.2em] text-teal-200">RentOS</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-semibold leading-tight md:text-5xl">List, lease, collect, and maintain — one platform for owners and renters.</h1>
        <p class="mt-4 max-w-2xl text-teal-100">Search published homes, apply online, sign a digital lease, pay rent, and track maintenance without leaving RentOS.</p>
        <form action="{{ route('listings.index') }}" class="mt-8 flex max-w-xl overflow-hidden rounded-full bg-white p-1 text-slate-900">
            <input name="q" placeholder="2-bed pet-friendly in Dhanmondi" class="flex-1 px-5 py-3 outline-none">
            <button class="rounded-full bg-teal-700 px-6 py-3 font-medium text-white">Search</button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-14">
    <div class="flex items-end justify-between">
        <h2 class="text-2xl font-semibold">Featured listings</h2>
        <a href="{{ route('listings.index') }}" class="text-sm text-teal-700">View all</a>
    </div>
    <div class="mt-6 grid gap-6 md:grid-cols-3">
        @forelse ($featured as $listing)
            @include('public.partials.listing-card', ['listing' => $listing])
        @empty
            <p class="text-slate-500">No featured homes yet. Seed the database or publish a listing.</p>
        @endforelse
    </div>
</section>
@endsection
