<?php namespace ItRail\AdTacker;

use System\Classes\PluginBase;
use ItRail\AdTacker\Classes\CustomHandler;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }


    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'AD Tracker',
                'description' => 'Settings for AD Tracker',
                'category'    => 'itrail.adtacker::lang.plugin.name',
                'icon'        => 'icon-user',
                'class'       => 'ItRail\AdTacker\Models\Settings',
                'order'       => 100,
                'permissions' => ['itrail.adtracker.manage_settings'],
            ]
        ];
    }
}
