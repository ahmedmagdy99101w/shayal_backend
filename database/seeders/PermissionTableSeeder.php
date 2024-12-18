<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'service-list',
            'create-service',
            'update-service',
            'delete-service',
            'option-type-list',
            'create-option-type',
            'update-option-type',
            'delete-option-type',
            'options-option-type',
            'subscription-list',
            'create-subscription',
            'update-subscription',
            'delete-subscription',
            'coupon-list',
            'create-coupon',
            'update-coupon',
            'delete-coupon',
            'area-list',
            'create-area',
            'update-area',
            'delete-area',
            'city-list',
            'create-city',
            'update-city',
            'delete-city',
            'booking-list',
            'control-booking-list',
            'create-control-booking',
            'update-control-booking',
            'delete-control-booking',
            'order-report-list',
            'payment-report-list',
            'review-report-list',
            'contact-us-notification',
            'order-notification',
            'register-notification',
            'manual-notification',
            'user-list',
            'create-user',
            'update-user',
            'delete-user',
            'role-list',
            'create-role',
            'update-role',
            'delete-role',
            'setting',
            'update-setting',
            'about-us',
            'update-about-us',
            'privacy',
            'update-privacy',
            'term',
            'update-term',
            'customer-service',
            'update-customer-service',
            'offer',
            'update-offer'
        ];


        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
