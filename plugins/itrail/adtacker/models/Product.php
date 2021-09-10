<?php namespace ItRail\AdTacker\Models;

use Model;

/**
 * Model
 */
class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_products';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachOne = [
        'photo' => \System\Models\File::class,
    ];
}
