<?php namespace Autumn\Social;

use RainLab\User\Models\User;
use System\Classes\PluginBase;

/**
 * Social Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];

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
        User::extend(function($model) {
            $model->hasMany['likes'] = ['Autumn\Social\Models\Like'];
            $model->hasMany['comments'] = ['Autumn\Social\Models\Comment'];
            $model->morphMany['followers'] = ['BBrand\Social\Models\Follow', 'name' => 'followable'];
            $model->belongsToMany['follows'] = [
                'RainLab\User\Models\User',
                'table'    => 'user_follows',
                'key'      => 'user_id',
                'otherKey' => 'followable_id'
            ];

            /*
            * Default options
            */
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
        });
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
        ];
    }

}
