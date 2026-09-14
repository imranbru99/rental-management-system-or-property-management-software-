<?php

namespace App\Services\Reviews;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    public function submit(array $attributes): Review
    {
        $review = Review::query()->create($attributes);

        $this->revealIfMutual($review);

        return $review->refresh();
    }

    public function reveal(Review $review): Review
    {
        $review->update([
            'is_revealed' => true,
            'revealed_at' => now(),
        ]);

        return $review->refresh();
    }

    protected function revealIfMutual(Review $review): void
    {
        $counterpart = Review::query()
            ->where('organization_id', $review->organization_id)
            ->where('id', '!=', $review->id)
            ->where('reviewer_id', '!=', $review->reviewer_id)
            ->where(function ($query) use ($review): void {
                $query->where(function ($inner) use ($review): void {
                    $inner->where('reviewee_type', User::class)
                        ->where('reviewee_id', $review->reviewer_id);
                })->orWhere(function ($inner) use ($review): void {
                    if ($review->reviewee_type === Property::class) {
                        $inner->where('reviewee_type', Property::class)
                            ->where('reviewee_id', $review->reviewee_id);
                    }
                });
            })
            ->first();

        if (! $counterpart) {
            return;
        }

        $this->reveal($review);
        $this->reveal($counterpart);
    }
}
