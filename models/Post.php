<?php namespace Autumn\Social\Models;

use Auth;
use Model;
use Uuid;

/**
 * Post model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Autumn\Social\Traits\RecordsActivity;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_social_posts';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'content' => 'required'
    ];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'created_at asc' => 'Created (ascending)',
        'created_at desc' => 'Created (descending)',
        'updated_at asc' => 'Updated (ascending)',
        'updated_at desc' => 'Updated (descending)',
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

    public $attachMany = [
        'images' => ['System\Models\File']
    ];

    public function beforeCreate()
    {
        $this->slug = Uuid::generate();
    }

    /**
     * Lists posts for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'    => 1,
            'perPage' => 30,
            'sort'    => 'created_at',
            'users'   => null,
        ], $options));

        /*
         * Sorting
         */
        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {
            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sort);

                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }

                list($sortField, $sortDirection) = $parts;
                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Users
         */
        if ($users !== null) {
            if (!is_array($users)) {
                $users = [$users];
            }

            $query->whereIn('user_id', $users);
        }
        return $query->paginate($perPage, $page);
    }

    public function addComment($content)
    {
        return Comment::add($content, $this);
    }

    public function afterDelete()
    {
        foreach($this->comments as $comment) {
            $comment->delete();
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

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param \Cms\Classes\Controller $controller
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

}