<?php namespace Autumn\Social\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Autumn\Social\Models\Activity;
use RainLab\User\Models\User as UserModel;
use ApplicationException;

class Activities extends ComponentBase
{
    /**
     * A collection of activities to display.
     *
     * @var Collection
     */
    public $activities;

    /**
     * Type of the activity stream. (user, dashboard)
     *
     * @var string
     */
    public $activityType;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Activities',
            'description' => 'Shows users activities on a page.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'slug'  => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for entries by its slug.',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ],
            'type' => [
                'label'       => 'Activity type',
                'description' => 'Activity type modes',
                'type'        => 'dropdown',
                'options'     => [
                    'user'      => 'User',
                    'dashboard' => 'Dashboard'
                ],
                'default'     => 'user'
            ],
            'limit' => [
                'title'             => 'Limit',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the limit value'
            ],
        ];
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        $this->activityType = $this->property('type');
        $this->prepareActivitiesList();
    }

    protected function prepareActivitiesList()
    {
        if ($this->activityType == 'user') {
            $user = UserModel::whereSlug($this->property('slug'))->first();
            $activities = Activity::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($this->property('limit'));
        }
        elseif ($this->activityType == 'dashboard') {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }

            $users = $user->follows->lists('id');

            $activities = Activity::whereIn('user_id', $users)
                ->orderBy('created_at', 'desc')
                ->paginate($this->property('limit'));
        }

        $this->activities = $this->page['activities'] = $activities;
    }

}