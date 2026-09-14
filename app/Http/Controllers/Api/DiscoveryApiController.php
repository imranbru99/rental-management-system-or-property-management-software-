<?php

namespace App\Http\Controllers\Api;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Enums\ViewingStatus;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Conversation;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Notice;
use App\Models\Viewing;
use App\Services\Leasing\LeaseSigningService;
use App\Services\Messaging\MessagingService;
use Illuminate\Http\Request;

class DiscoveryApiController extends Controller
{
    public function favorites(Request $request)
    {
        return $request->user()->favorites()->with('property')->paginate(20);
    }

    public function unfavorite(Request $request, Listing $listing)
    {
        $request->user()->favorites()->detach($listing->id);

        return ['ok' => true];
    }

    public function savedSearches(Request $request)
    {
        return $request->user()->savedSearches()->latest()->paginate(20);
    }

    public function storeSavedSearch(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'criteria' => ['required', 'array'],
            'alert_frequency' => ['nullable', 'in:off,daily,weekly'],
        ]);

        return $request->user()->savedSearches()->create([
            'name' => $data['name'],
            'criteria' => $data['criteria'],
            'alert_frequency' => $data['alert_frequency'] ?? 'daily',
        ]);
    }

    public function viewings(Request $request)
    {
        return Viewing::query()
            ->where('applicant_id', $request->user()->id)
            ->with('listing')
            ->latest()
            ->paginate(20);
    }

    public function storeViewing(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'type' => ['nullable', 'in:in_person,video'],
            'notes' => ['nullable', 'string'],
        ]);

        return $listing->viewings()->create([
            ...$data,
            'organization_id' => $listing->organization_id,
            'property_id' => $listing->property_id,
            'applicant_id' => $request->user()->id,
            'type' => $data['type'] ?? 'in_person',
            'status' => ViewingStatus::Requested,
        ]);
    }

    public function announcements(Request $request)
    {
        $orgIds = $request->user()->leases()->pluck('leases.organization_id');
        $propertyIds = $request->user()->leases()->pluck('leases.property_id');

        return Announcement::query()
            ->whereNotNull('published_at')
            ->whereIn('organization_id', $orgIds)
            ->where(function ($query) use ($propertyIds): void {
                $query->whereNull('property_id')->orWhereIn('property_id', $propertyIds);
            })
            ->latest('published_at')
            ->paginate(20);
    }

    public function notices(Request $request)
    {
        $leaseIds = $request->user()->leases()->pluck('leases.id');

        return Notice::query()
            ->where(function ($query) use ($request, $leaseIds): void {
                $query->where('user_id', $request->user()->id)
                    ->orWhereIn('lease_id', $leaseIds);
            })
            ->latest()
            ->paginate(20);
    }

    public function storeNotice(Request $request)
    {
        $data = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'effective_on' => ['required', 'date'],
            'body' => ['required', 'string'],
        ]);

        $lease = Lease::query()->findOrFail($data['lease_id']);
        abort_unless($lease->primary_tenant_id === $request->user()->id, 403);

        return Notice::query()->create([
            ...$data,
            'organization_id' => $lease->organization_id,
            'user_id' => $request->user()->id,
            'type' => NoticeType::Vacate,
            'status' => NoticeStatus::Submitted,
        ]);
    }

    public function signLease(Request $request, Lease $lease, LeaseSigningService $signing)
    {
        abort_unless($lease->primary_tenant_id === $request->user()->id, 403);

        return $signing->sign($lease, $request->user(), 'tenant');
    }

    public function conversations(Request $request)
    {
        return Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $request->user()->id))
            ->with('messages')
            ->latest('last_message_at')
            ->paginate(20);
    }

    public function reply(Request $request, Conversation $conversation, MessagingService $messages)
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        return $messages->reply($conversation, $request->user(), $data['body']);
    }
}
