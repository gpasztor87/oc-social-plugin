<?php namespace Autumn\Social\Components;

use Auth;
use Request;
use Redirect;
use Validator;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use Autumn\Social\Models\Post as PostModel;
use ValidationException;
use ApplicationException;

class WallStream extends ComponentBase
{
    /**
     * A collection of posts to display.
     *
     * @var Collection
     */
    public $posts;

    /**
     * Type of the wall stream. (user, dashboard)
     *
     * @var string
     */
    public $wallType;

    /**
     * Reference to the page name for linking to posts.
     *
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to profiles.
     *
     * @var string
     */
    public $profilePage;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Wall stream',
            'description' => 'Wall stream are used to display contents / activities on a page.'
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
            'postPage' => [
                'title'       => 'Post page',
                'description' => 'Name of the post page file.',
                'type'        => 'dropdown'
            ],
            'profilePage' => [
                'title'       => 'Profile page',
                'description' => 'Name of the profile page file.',
                'type'        => 'dropdown'
            ],
            'type' => [
                'label'       => 'Wall type',
                'description' => 'Wall type modes',
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
        $this->prepareWallStream();
    }

    protected function prepareVars()
    {
        $this->wallType = $this->property('type');
        $this->postPage = $this->property('postPage');
        $this->profilePage = $this->property('profilePage');
    }


    protected function prepareWallStream()
    {
        $this->prepareVars();

        if ($this->wallType == 'user' && $this->property('slug')) {
            $users = UserModel::whereSlug($this->property('slug'))->first()->id;
        }
        elseif ($this->wallType == 'dashboard') {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }

            $users = $user->follows->lists('id');
            $users[] = $user->id;
        }

        /*
         * List all the posts, eager load their comments
         */
        $currentPage = input('page');
        $searchString = trim(input('search'));
        $posts = PostModel::with('comments')->listFrontEnd([
            'page'    => $currentPage,
            'perPage' => $this->property('limit'),
            'search'  => $searchString,
            'users'   => $users
        ]);

        /*
         * Add a "url" helper attribute for linking to each post
         */
        $posts->each(function($post) {
            $post->setUrl($this->postPage, $this->controller);
            $post->user->setUrl($this->profilePage, $this->controller);

            $post->comments->each(function($comment) {
                $comment->user->setUrl($this->profilePage, $this->controller);
            });
        });

        $this->posts = $this->page['posts'] = $posts;

        /*
         * Pagination
         */
        if ($posts) {
            $queryArr = [];
            if ($searchString) {
                $queryArr['search'] = $searchString;
            }

            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);
            if ($currentPage > ($lastPage = $posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
        }

        return $posts;
    }

    public function onCreatePost() {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $rules = [
            'content' => 'required'
        ];

        $validation = Validator::make(input(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $post = new PostModel;
        $post->user = $user;
        $post->content = input('content');
        $post->save();

        $this->prepareWallStream();
    }

    public function onUpdatePost() {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $post = PostModel::find(input('post'));

        if (!$post->canEdit()) {
            throw new ApplicationException('Permission denied.');
        }

        /*
         * Supported modes: edit, view, delete, save
         */
        $mode = input('mode', 'edit');

        if ($mode == 'save') {
            $post->fill(input());
            $post->save();
        }
        elseif ($mode == 'delete') {
            $post->delete();
        }

        $this->page['mode'] = $mode;
        $this->page['post'] = $post;

        $this->prepareWallStream();
    }

}