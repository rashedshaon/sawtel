<?php namespace ItRail\AdTacker\Models;

use Model;
use Backend\Models\User;

/**
 * Model
 */
class WithdrawRequest extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_withdraw_requests';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasOne = [
        'bank' => ['ItRail\AdTacker\Models\Bank', 'key' => 'id', 'otherKey' => 'bank_id'],
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

    public function getBankIdOptions()
    {
        $options = [
            null => 'Select Bank',
        ];
        $items = new Bank();

        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = $item->name;
        });

        return $options;
    }
}
