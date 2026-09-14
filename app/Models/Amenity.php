<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use ImranDev\UniversalSlug\Attributes\Slug;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable(['name', 'slug', 'icon', 'category'])]
#[Slug(from: 'name', to: 'slug')]
class Amenity extends Model
{
    use HasUniversalSlug;
}
