<?php namespace Autumn\Social\Models;

use Model;

/**
 * Activity model
 */
class Activity extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'social_activities';

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    public $morphTo = ['subject'];

}