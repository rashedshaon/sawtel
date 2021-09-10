<?php namespace ItRail\AdTacker\Models;

use Model;

/**
 * Model
 */
class OrderStatus extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_order_statuses';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
