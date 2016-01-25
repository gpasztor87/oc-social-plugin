<?php namespace Autumn\Social\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Autumn\Social\Models\Follow;
use RainLab\User\Models\User as UserModel;
use ApplicationException;

class Profile extends ComponentBase
{
    /**
     * The user model used for display.
     *
     * @var \RainLab\User\Models\User
     */
    public $profile;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Profile',
            'description' => 'Displays the user\'s Profile on a page.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'slug'    => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the profile by its slug.',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ]
        ];
    }

    /**
     * Executed when this component is first initialized, before AJAX requests.
     */
    public function init()
    {
        if (Auth::check() && $this->getProfile()->id == Auth::getUser()->id) {
            $component = $this->addComponent(
                'Responsiv\Uploader\Components\ImageUploader',
                'imageUploader',
                ['deferredBinding' => false, 'imageWidth' => 150, 'imageHeight' => 150]
            );

            $component->bindModel('avatar', Auth::getUser());
        }
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->profile = $this->page['profile'] = $this->getProfile();

        $notifications = Auth::getUser()->notifications;
        $this->page['notifications'] = $notifications;
        //dd($notifications);
    }

    protected function getProfile()
    {
        $slug = $this->property('slug');
        return UserModel::whereSlug($slug)->first();
    }

    public function onFollow()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        Follow::toggle($user, UserModel::find(input('id')));
        $this->prepareVars();
    }

}