<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingApiController extends Controller
{
    public function index(Request $request)
    {
        return Listing::query()
            ->published()
            ->with('property')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->latest('published_at')
            ->paginate(20);
    }

    public function show(Listing $listing)
    {
        return $listing->load('property.organization');
    }

    public function favorite(Request $request, Listing $listing)
    {
        $request->user()->favorites()->syncWithoutDetaching([$listing->id]);

        return ['ok' => true];
    }
}
