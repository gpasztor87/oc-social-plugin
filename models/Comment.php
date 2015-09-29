<?php namespace Autumn\Social\Models;

use Auth;
use Event;
use Model;

/**
 * Comment model
 */
class Comment extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'social_comments';

    /**
     * @var array Hidden fields from array/json access
     */
    protected $hidden = ['commentable_id', 'commentable_type'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'content' => 'required|min:2'
    ];

    /**
     * @var array Relations
     */
    public $morphTo = ['commentable'];

    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

} 