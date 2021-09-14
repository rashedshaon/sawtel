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

    public function getPriceLabelAttribute()
    {
        return number_format($this->price);
    }

    public function scopeIsPublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function scopeIsFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    public function getPhoto($imageWidth = null, $imageHeight = null)
    {
        return isset($this->photo) ? (($imageHeight && $imageWidth) ? $this->photo->getThumb($imageWidth, $imageHeight, ['mode' => 'crop']) : $this->photo->getPath()) :  (($imageHeight && $imageWidth) ? "https://dummyimage.com/$imageWidth"."x"."$imageHeight/e3e3e3/0A67B2.jpg&text=++AD++" : "https://dummyimage.com/200x240/e3e3e3/0A67B2.jpg&text=++AD++");
    }
}
