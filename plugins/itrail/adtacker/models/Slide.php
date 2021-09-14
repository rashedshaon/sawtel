<?php namespace ItRail\AdTacker\Models;

use Model;

/**
 * Model
 */
class Slide extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'itrail_adtacker_slides';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachOne = [
        'photo' => \System\Models\File::class,
    ];

    public function scopeIsPublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function getPhoto($imageWidth = null, $imageHeight = null)
    {
        return isset($this->photo) ? (($imageHeight && $imageWidth) ? $this->photo->getThumb($imageWidth, $imageHeight, ['mode' => 'crop']) : $this->photo->getPath()) :  (($imageHeight && $imageWidth) ? "https://dummyimage.com/$imageWidth"."x"."$imageHeight/e3e3e3/0A67B2.jpg&text=++Slide++" : "https://dummyimage.com/600x400/e3e3e3/0A67B2.jpg&text=++Slide++");
    }
}
