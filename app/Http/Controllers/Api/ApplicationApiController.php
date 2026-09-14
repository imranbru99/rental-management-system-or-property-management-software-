<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Listing;
use Illuminate\Http\Request;

class ApplicationApiController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'monthly_income' => ['nullable', 'integer'],
            'employer' => ['nullable', 'string'],
            'employment_status' => ['nullable', 'string'],
            'occupants' => ['nullable', 'integer', 'min:1'],
            'has_pets' => ['boolean'],
            'consent_background_check' => ['accepted'],
        ]);

        $application = Application::query()->create([
            ...$data,
            'organization_id' => $listing->organization_id,
            'listing_id' => $listing->id,
            'property_id' => $listing->property_id,
            'unit_id' => $listing->unit_id,
            'applicant_id' => $request->user()->id,
            'status' => ApplicationStatus::Submitted,
        ]);

        return response()->json($application, 201);
    }

    public function index(Request $request)
    {
        return Application::query()
            ->where('applicant_id', $request->user()->id)
            ->with(['listing', 'property'])
            ->latest()
            ->paginate(20);
    }
}
