<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Enums\ViewingStatus;
use App\Models\Application;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Services\Ai\ListingAiService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicListingController extends Controller
{
    public function home(): View
    {
        $featured = Listing::query()
            ->published()
            ->with('property')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(6)
            ->get();

        $latest = Listing::query()
            ->published()
            ->with('property')
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('public.home', compact('featured', 'latest'));
    }

    public function index(Request $request): View
    {
        $listings = $this->filteredListings($request)
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.listings.index', compact('listings'));
    }

    public function show(Listing $listing): View
    {
        abort_unless($listing->status === ListingStatus::Published, 404);

        $listing->load('property.organization');
        $isFavorite = auth()->check() && auth()->user()->favorites()->where('listings.id', $listing->id)->exists();

        return view('public.listings.show', compact('listing', 'isFavorite'));
    }

    public function search(Request $request, ListingAiService $ai)
    {
        $q = $request->string('q')->toString();

        return redirect()->route('listings.index', ['q' => $q]);
    }

    public function apply(Request $request, Listing $listing): RedirectResponse
    {
        $this->assertTenant();

        abort_unless($listing->status === ListingStatus::Published, 404);

        $data = $request->validate([
            'monthly_income' => ['nullable', 'integer', 'min:0'],
            'employer' => ['nullable', 'string', 'max:255'],
            'occupants' => ['required', 'integer', 'min:1'],
            'has_pets' => ['sometimes', 'boolean'],
            'consent_background_check' => ['accepted'],
        ]);

        Application::query()->create([
            ...$data,
            'has_pets' => $request->boolean('has_pets'),
            'organization_id' => $listing->organization_id,
            'listing_id' => $listing->id,
            'property_id' => $listing->property_id,
            'unit_id' => $listing->unit_id,
            'applicant_id' => auth()->id(),
            'status' => ApplicationStatus::Submitted,
        ]);

        return back()->with('status', 'Application submitted. Track it in the tenant portal.');
    }

    public function requestViewing(Request $request, Listing $listing): RedirectResponse
    {
        $this->assertTenant();

        abort_unless($listing->status === ListingStatus::Published, 404);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'type' => ['required', 'in:in_person,video'],
            'notes' => ['nullable', 'string'],
        ]);

        $listing->viewings()->create([
            'organization_id' => $listing->organization_id,
            'property_id' => $listing->property_id,
            'applicant_id' => auth()->id(),
            'type' => $data['type'],
            'status' => ViewingStatus::Requested,
            'scheduled_at' => $data['scheduled_at'],
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Viewing requested. The owner will confirm a time.');
    }

    public function favorite(Listing $listing): RedirectResponse
    {
        $this->assertTenant();

        auth()->user()->favorites()->toggle([$listing->id]);

        return back()->with('status', 'Favorites updated.');
    }

    public function saveSearch(Request $request): RedirectResponse
    {
        $this->assertTenant();

        SavedSearch::query()->create([
            'user_id' => auth()->id(),
            'name' => $request->string('q')->toString() ?: 'Saved search',
            'criteria' => [
                'q' => $request->string('q')->toString() ?: null,
                'min_bedrooms' => $request->integer('min_bedrooms') ?: null,
                'max_rent' => $request->integer('max_rent') ?: null,
                'pets' => $request->boolean('pets'),
                'furnished' => $request->boolean('furnished'),
            ],
            'alert_frequency' => 'daily',
        ]);

        return back()->with('status', 'Search saved. Daily alerts run from the tenant portal.');
    }

    protected function filteredListings(Request $request)
    {
        return Listing::query()
            ->published()
            ->with('property')
            ->when($request->string('q')->toString(), function ($query, string $q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('property', fn ($property) => $property->where('city', 'like', "%{$q}%"));
                });
            })
            ->when($request->integer('min_bedrooms'), fn ($query, $beds) => $query->whereHas('property', fn ($p) => $p->where('bedrooms', '>=', $beds)))
            ->when($request->integer('max_rent'), fn ($query, $rent) => $query->where('rent', '<=', $rent))
            ->when($request->boolean('pets'), fn ($query) => $query->whereHas('property', fn ($p) => $p->where('pet_friendly', true)))
            ->when($request->boolean('furnished'), fn ($query) => $query->whereHas('property', fn ($p) => $p->where('furnished', true)));
    }

    protected function assertTenant(): void
    {
        if (! auth()->check()) {
            throw new HttpResponseException(redirect()->guest(url('/tenant/login')));
        }

        abort_unless(auth()->user()->role === UserRole::Tenant, 403, 'Sign in as a tenant to continue.');
    }
}
