<?php

namespace App\Filament\Tenant\Resources\Announcements\Pages;

use App\Filament\Tenant\Resources\Announcements\AnnouncementResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAnnouncements extends ManageRecords
{
    protected static string $resource = AnnouncementResource::class;
}
