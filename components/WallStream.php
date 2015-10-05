<?php namespace Autumn\Social\Components;

use Cms\Classes\ComponentBase;

class WallStream extends ComponentBase
{

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

        ];
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {

    }

}