<?php namespace ItRail\AdTacker\Models;

use Model;
use Backend\Models\User;

/**
 * Model
 */
class Transaction extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_transactions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasOne = [
        'type' => ['ItRail\AdTacker\Models\TransactionType', 'key' => 'id', 'otherKey' => 'type_id'],
        'user' => ['Backend\Models\User', 'key' => 'id', 'otherKey' => 'user_id'],
    ];

    public function getUserIdOptions()
    {
        $options = [
            null => 'Select user',
        ];
        $items = new User();

        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = $item->name;
        });

        return $options;
    }

    public function getTypeIdOptions()
    {
        $options = [
            null => 'Select type',
        ];
        $items = new TransactionType();

        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = $item->name;
        });

        return $options;
    }
}
