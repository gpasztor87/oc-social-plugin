<?php namespace Autumn\Social\Models;

use Model;

/**
 * Post model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'social_posts';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'content' => 'required'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user'  => ['RainLab\User\Models\User']
    ];

    public $morphMany = [
        'likes' => [
            'Autumn\Social\Models\Like',
            'name' => 'likeable'
        ],
        'comments' => [
            'Autumn\Social\Models\Comment',
            'name' => 'commentable'
        ],
    ];

}