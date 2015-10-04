<?php namespace Autumn\Social\Components;

use Auth;
use Cms\Classes\ComponentBase;

class Profile extends ComponentBase
{
    use \Autumn\Social\Traits\ComponentUtils;

    /**
     * @var bool Has the model been bound.
     */
    protected $deferredBinding = false;

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
            ],
            'maxSize'     => [
                'title'       => 'Max file size (MB)',
                'description' => 'The maximum file size that can be uploaded in megabytes.',
                'type'        => 'string',
                'default'     => '5'
            ],
            'fileTypes'   => [
                'title'       => 'Supported file types',
                'description' => 'File extensions separated by commas (,) or star (*) to allow all types.',
                'type'        => 'string',
                'default'     => '.gif,.jpg,.jpeg,.png'
            ]
        ];
    }

    /**
     * Executed when this component is first initialized, before AJAX requests.
     */
    public function init()
    {
        $this->bindModel('avatar', Auth::getUser());
        $this->fileTypes = $this->processFileTypes();
        $this->maxSize = $this->property('maxSize');
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {

    }

    public function onUploadAvatar()
    {
        $image = $this->getPopulated();

        if (($deleteId = input('id')) && input('mode') == 'delete') {
            if ($deleteImage = $image->find($deleteId)) {
                $deleteImage->delete();
            }
        }

        $this->page['image'] = $image;
    }

}