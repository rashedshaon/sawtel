<?php namespace ItRail\AdTacker\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class WithdrawRequests extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'itrail.adtracker.manage_withdraw_requests' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('ItRail.AdTacker', 'main-menu-item', 'side-menu-item5');
    }
}