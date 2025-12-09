<?php

namespace App\Actions;

class GetPermissionForTable
{
    private static array $permissionMap = [
        'ads' => 'pi pi-megaphone|/ads',
        'categories' => 'pi pi-sitemap|/categories',
        'cities' => 'fa-solid fa-city|/cities',
        'contacts'=>'pi pi-send|/contacts',
        'countries' => 'fa-solid fa-earth-americas|/countries',
        'coupons' => 'pi pi-bookmark-fill|/coupons',
        'users' => 'pi pi-users|/users',
        'orders'=>'pi pi-cart-plus|/orders',
        'properties' => 'pi pi-wrench|/properties',
        'properties_heading' => 'pi pi-palette|/properties-headings',
        'roles' => 'pi pi-shield|/roles',
        'services' => 'pi pi-cog|/services',
        'shipment_prices' => 'fa-solid fa-hand-holding-dollar|/shipment',
        'sms_histories' => 'fa-solid fa-comment-sms|/dynamic-sms',
    ];

    public static function handle(string $table, string $action): ?string
    {
        if (isset(self::$permissionMap[$table])) {
            return self::$permissionMap[$table] . '|' . $action;
        }
        return null;
    }
}
