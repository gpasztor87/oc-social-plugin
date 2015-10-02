<?php namespace Autumn\Social\Models;

use Model;

/**
 * Like model
 */
class Like extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'user_likes';

    /**
     * @var array Hidden fields from array/json access
     */
    protected $hidden = ['likeable_id', 'likeable_type'];

    /**
     * @var array Relations
     */
    public $morphTo = ['likeable'];

    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

} 