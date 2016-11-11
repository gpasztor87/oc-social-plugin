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
    public $table = 'autumn_social_activities';

    /**
     * @var array Hidden fields from array/json access
     */
    protected $hidden = ['subject_id', 'subject_type'];


    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    public $morphTo = [
        'subject' => []
    ];

}
