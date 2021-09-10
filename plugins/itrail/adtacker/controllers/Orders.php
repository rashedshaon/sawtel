<?php namespace ItRail\AdTacker\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Orders extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'itrail.adtracker.manage_orders' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('ItRail.AdTacker', 'main-menu-item', 'side-menu-item3');

        $this->addCss('/plugins/itrail/adtacker/assets/css/style.css');
    }
}
