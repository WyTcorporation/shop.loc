<?php

namespace App\Enums;

enum Role: string
{
    case Administrator = 'administrator';
    case Accountant = 'accountant';
    case CatalogManager = 'catalog manager';
    case OrderManager = 'order manager';
    case MarketingManager = 'marketing manager';
    case Support = 'support';
}
