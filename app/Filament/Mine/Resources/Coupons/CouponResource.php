<?php

namespace App\Filament\Mine\Resources\Coupons;

use App\Filament\Mine\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Mine\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Mine\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Mine\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Mine\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static string|null|\UnitEnum $navigationGroup = 'Marketing';
    protected static ?string $recordTitleAttribute = 'code';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Coupon::count();
    }
}
