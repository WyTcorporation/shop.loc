<?php

namespace App\Enums;

enum Permission: string
{
    case ViewProducts = 'view products';
    case ManageProducts = 'manage products';
    case ViewOrders = 'view orders';
    case ManageOrders = 'manage orders';
    case ViewUsers = 'view users';
    case ManageUsers = 'manage users';
    case ViewInventory = 'view inventory';
    case ManageInventory = 'manage inventory';
    case ViewCoupons = 'view coupons';
    case ManageCoupons = 'manage coupons';
    case ViewReviews = 'view reviews';
    case ManageReviews = 'manage reviews';
    case ViewVendors = 'view vendors';
    case ManageVendors = 'manage vendors';
    case ViewCategories = 'view categories';
    case ManageCategories = 'manage categories';
    case ManageSettings = 'manage settings';
    case ViewInvoices = 'view invoices';
    case ManageInvoices = 'manage invoices';
    case ViewDeliveryNotes = 'view delivery notes';
    case ManageDeliveryNotes = 'manage delivery notes';
    case ViewActs = 'view acts';
    case ManageActs = 'manage acts';
    case ViewSaftExports = 'view saft exports';
    case ManageSaftExports = 'manage saft exports';
    case ViewMarketing = 'view marketing';
    case ManageMarketing = 'manage marketing';
    case ViewCampaigns = 'view campaigns';
    case ManageCampaigns = 'manage campaigns';
    case ViewSegments = 'view segments';
    case ManageSegments = 'manage segments';
    case ViewCampaignTemplates = 'view campaign templates';
    case ManageCampaignTemplates = 'manage campaign templates';
    case ViewCampaignTests = 'view campaign tests';
    case ManageCampaignTests = 'manage campaign tests';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
