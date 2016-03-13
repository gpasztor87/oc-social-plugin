<?php namespace Autumn\Social\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Autumn\Social\Models\Follow;
use RainLab\User\Models\User as UserModel;
use ApplicationException;

/**
 * Profiles component
 */
class Profiles extends ComponentBase
{
    /**
     * A collection of profiles to display.
     *
     * @var \October\Rain\Database\Collection
     */
    public $profiles;

    /**
     * Reference to the page name for linking to profiles.
     *
     * @var string
     */
    public $profilePage;

    /**
     * Message to display when there are no profiles.
     *
     * @var string
     */
    public $noProfilesMessage;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Profile List',
            'description' => 'Displays a list of users profile on a page.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'noProfilesMessage'  => [
                'title'       => 'No profiles message',
                'description' => 'Message to display in the profiles list in case if there are no users.',
                'type'        => 'string',
                'default'     => 'No users found'
            ],
            'profilePage' => [
                'title'       => 'Profile page',
                'description' => 'Name of the profile page file.',
                'type'        => 'dropdown'
            ],
            'profilesPerPage' => [
                'title'             => 'Profiles per page',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the profiles per page value'
            ],
        ];
    }

    public function getProfilePageOptions()
    {
        return Page::withComponent('socialProfile')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        $this->prepareVars();

        return $this->prepareProfileList();
    }

    protected function prepareVars()
    {
        $this->noProfilesMessage = $this->property('noProfilesMessage');
        $this->profilePage = $this->property('profilePage');
    }

    /**
     * Returns the logged in user, if available.
     */
    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    protected function prepareProfileList()
    {
        $currentPage = input('page');
        $searchString = trim(input('search'));
        $profiles = UserModel::listFrontEnd([
            'page' => $currentPage,
            'perPage' => $this->property('profilesPerPage'),
            'search' => $searchString,
        ]);

        /*
         * Add a "url" helper attribute for linking to each profile
         */
        $profiles->each(function($profile) {
            $profile->setUrl($this->profilePage, $this->controller);
        });

        $this->page['profiles'] = $this->profiles = $profiles;

        /*
         * Pagination
         */
        if ($profiles) {
            $queryArr = [];
            if ($searchString) {
                $queryArr['search'] = $searchString;
            }

            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);
            if ($currentPage > ($lastPage = $profiles->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
        }
    }

    public function onSearch()
    {
        return $this->prepareProfileList();
    }

    public function onFollow()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        Follow::toggle($user, UserModel::find(input('id')));
    }

}