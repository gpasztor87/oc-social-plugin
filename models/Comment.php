<?php namespace Autumn\Social\Models;

use Auth;
use Model;
use Notification;

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
    protected $table = 'autumn_social_comments';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

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
    public $morphTo = [
        'commentable' => []
    ];

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

    public static function add($content, $commentable)
    {
        $comment = new Comment;
        $comment->content = $content;
        $comment->user = $user = Auth::getUser();
        $comment->commentable_id = $commentable->id;
        $comment->commentable_type = get_class($commentable);
        $comment->save();

        Notification::create('comment_posted', $user, $commentable, $commentable->user);
    }

    public function afterDelete()
    {
        foreach($this->likes as $like) {
            $like->delete();
        }
    }

    public function canEdit($user = null)
    {
        if ($user === null) {
            $user = Auth::getUser();
        }

        if (!$user) {
            return false;
        }

        return $this->user_id == $user->id;
    }

} 