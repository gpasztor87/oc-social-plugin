<?php namespace Autumn\Social;

use Str;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use Autumn\Social\Models\Follow;
use Autumn\Social\Models\Like;

/**
 * Social Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User', 'Responsiv.Uploader', 'Autumn.Notifications'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Social plugin',
            'description' => 'Social network system.',
            'author'      => 'Autumn',
            'icon'        => 'icon-child'
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        $this->extendUserModel();
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Autumn\Social\Components\Profile'  => 'socialProfile',
            'Autumn\Social\Components\Profiles' => 'socialProfiles',
            'Autumn\Social\Components\ActivityStream' => 'socialActivityStream',
            'Autumn\Social\Components\WallStream' => 'socialWallStream',
        ];
    }

    public function registerNotifications()
    {
        return [
            'Autumn\Social\Notifications\CommentPosted' => 'comment_posted',
            //'Autumn\Social\Notifications\PostLiked' => 'post_liked'
        ];
    }

    protected function extendUserModel()
    {
        User::extend(function($model) {
            $model->hasMany['likes'] = ['Autumn\Social\Models\Like'];
            $model->hasMany['comments'] = ['Autumn\Social\Models\Comment'];
            $model->morphMany['followers'] = ['Autumn\Social\Models\Follow', 'name' => 'followable'];
            $model->belongsToMany['follows'] = [
                'RainLab\User\Models\User',
                'table'    => 'autumn_social_follows',
                'key'      => 'user_id',
                'otherKey' => 'followable_id'
            ];

            $model->addDynamicMethod('scopeListFrontEnd', function($query, $options) {
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
            });

            $model->addDynamicMethod('beforeCreate', function() use($model) {
                $model->slug = Str::slug($model->username);
            });

            $model->addDynamicMethod('setUrl', function($pageName, $controller) use($model) {
                $params = [
                    'id'   => $model->id,
                    'slug' => $model->slug
                ];

                return $model->url = $controller->pageUrl($pageName, $params);
            });

            $model->addDynamicMethod('isFollowing', function($user) use($model) {
                return Follow::check($model, $user);
            });

            $model->addDynamicMethod('isLiking', function($target) use($model) {
                return Like::check($model, $target);
            });
        });
    }

}
