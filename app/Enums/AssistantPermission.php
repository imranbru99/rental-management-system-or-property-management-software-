<?php

namespace App\Enums;

enum AssistantPermission: string
{
    case ViewFinances = 'view_finances';
    case CollectRent = 'collect_rent';
    case ApproveMaintenance = 'approve_maintenance';
    case SignLeases = 'sign_leases';
    case ManageListings = 'manage_listings';
    case ProcessApplications = 'process_applications';
    case ManageStaff = 'manage_staff';
    case MessageTenants = 'message_tenants';
    case ManageVendors = 'manage_vendors';
    case ViewReports = 'view_reports';
    case ManageDocuments = 'manage_documents';
    case ScheduleShowings = 'schedule_showings';
    case LogInspections = 'log_inspections';

    /**
     * @return list<string>
     */
    public static function defaultFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::SuperAdmin, UserRole::OrgAdmin, UserRole::Owner => array_column(self::cases(), 'value'),
            UserRole::Assistant, UserRole::Agent => [
                self::MessageTenants->value,
                self::ManageListings->value,
                self::ScheduleShowings->value,
                self::LogInspections->value,
                self::ManageDocuments->value,
            ],
            default => [],
        };
    }
}
