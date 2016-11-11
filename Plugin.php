<?php namespace Autumn\Social;

use System\Classes\PluginBase;
use RainLab\User\Models\User;

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
            'Autumn\Social\Notifications\PostLiked' => 'post_liked'
        ];
    }

    protected function extendUserModel()
    {
        User::extend(function($model) {
            $model->implement[] = 'Autumn\Social\Behaviors\SocialModel';
        });
    }

}
