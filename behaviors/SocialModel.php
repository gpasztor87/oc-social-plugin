<?php namespace Autumn\Social\Behaviors;

use Str;
use Autumn\Social\Models\Like;
use Autumn\Social\Models\Follow;
use System\Classes\ModelBehavior;

class SocialModel extends ModelBehavior
{
    /**
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->hasMany['likes'] = ['Autumn\Social\Models\Like'];
        $model->hasMany['comments'] = ['Autumn\Social\Models\Comment'];
        $model->morphMany['followers'] = [
            'Autumn\Social\Models\Follow',
            'name' => 'followable'
        ];
        $model->belongsToMany['follows'] = [
            'RainLab\User\Models\User',
            'table'    => 'autumn_social_follows',
            'key'      => 'user_id',
            'otherKey' => 'followable_id'
        ];
    }

    public function beforeCreate()
    {
        $this->model->slug = Str::slug($this->model->username);
    }

    public function scopeListFrontEnd($query, $options)
    {
        extract(array_merge([
            'page'    => 1,
            'perPage' => 10,
            'sort'    => 'name',
            'search'  => ''
        ], $options));

        /*
        * Sorting
        */
        $allowedSortingOptions = ['created_at', 'updated_at'];

        if (!in_array($sort, $allowedSortingOptions)) {
            $sort = $allowedSortingOptions[0];
        }

        $query->orderBy($sort, in_array($sort, ['name', 'created_at', 'updated_at']) ? 'desc' : 'asc');

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, ['name', 'username']);
        }

        return $query->paginate($perPage, $page);
    }

    public function isFollowing($user)
    {
        return Follow::check($this->model, $user);
    }

    public function isLiking($target)
    {
        return Like::check($this->model, $target);
    }

    /**
     * Sets the "url" attribute with a URL to this object.
     *
     * @param string $pageName
     * @param \Cms\Classes\Controller $controller
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->model->id,
            'slug' => $this->model->slug,
        ];

        return $this->model->url = $controller->pageUrl($pageName, $params);
    }
}