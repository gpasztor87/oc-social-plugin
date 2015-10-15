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

    public $morphTo = ['subject'];

    public function getSubjectAttribute()
    {
        return call_user_func([$this->subject_type, 'find'], $this->subject_id);
    }

}