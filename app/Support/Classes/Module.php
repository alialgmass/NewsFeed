<?php

namespace App\Support\Classes;

class Module
{
    public static function registered(): array
    {

        return [
            'Admin',
            'Auth',
            'Gateway',
            'Permission',
            'SallaIntegration',
            'Team',
            'User',
            'Whatsapp',
            'Dashboard',
            'Notification',
            'Customer',
            'Campaign',
            'Profile',
            'Subscription',
            'Package',
            'Payment',
            'FeatureToggle',
            'Currency',
            'ZidIntegration',
            'Wallet',
        ];
    }
}
