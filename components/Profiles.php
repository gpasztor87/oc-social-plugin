<?php namespace Autumn\Social\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;

/**
 * Profiles component
 */
class Profiles extends ComponentBase
{
    /**
     * A collection of profiles to display.
     *
     * @var Collection
     */
    public $profiles;

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
                'description' => 'Message to display in the profiles list in case if there are no users. This property is used by the default component partial.',
                'type'        => 'string',
                'default'     => 'No users found'
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

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        return $this->prepareProfileList();
    }

    /**
     * Returns the logged in user, if available
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
        else {
            $this->noProfilesMessage = $this->property('noProfilesMessage');
        }
    }

    public function onSearch()
    {
        return $this->prepareProfileList();
    }

}