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
    use \Autumn\Social\Traits\RecordsActivity;

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

    public $morphMany = [
        'likes' => [
            'Autumn\Social\Models\Like',
            'name' => 'likeable'
        ]
    ];

    /**
     * Which events to record for the auth'd user.
     *
     * @var array
     */
    protected static $recordEvents = ['created'];

    public function getCommentableAttribute()
    {
        return call_user_func([$this->commentable_type, 'find'], $this->commentable_id);
    }

} 