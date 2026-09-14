<a href="{{ route('listings.show', $listing) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="h-40 bg-gradient-to-br from-teal-100 to-slate-200"></div>
    <div class="p-4">
        <p class="text-xs uppercase tracking-wide text-teal-700">{{ $listing->property?->city }}</p>
        <h3 class="mt-1 font-semibold">{{ $listing->title }}</h3>
        <p class="mt-2 text-sm text-slate-500">{{ $listing->property?->bedrooms }} bed · {{ $listing->property?->bathrooms }} bath</p>
        <p class="mt-3 text-lg font-semibold text-slate-900">{{ \App\Support\Money::format($listing->rent, $listing->currency) }}<span class="text-sm font-normal text-slate-500"> / month</span></p>
    </div>
</a>
