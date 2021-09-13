<?php namespace ItRail\AdTacker\Models;

use Model;

/**
 * Model
 */
class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_orders';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasOne = [
        'status' => ['ItRail\AdTacker\Models\OrderStatus', 'key' => 'id', 'otherKey' => 'status_id'],
        'user' => ['Backend\Models\User', 'key' => 'id', 'otherKey' => 'user_id'],
    ];

    public $hasMany = [
        'items' => ['ItRail\AdTacker\Models\OrderItem', 'key' => 'order_id', 'otherKey' => 'id'],
    ];

    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }

    public function getTotalAmountAttribute()
    {
        return $this->items()->sum('price');
    }
}
