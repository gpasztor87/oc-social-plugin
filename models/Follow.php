<?php namespace Autumn\Social\Models;

use Event;
use Model;

/**
 * Follow model
 */
class Follow extends Model
{
    use \Autumn\Social\Traits\RecordsActivity;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_social_follows';

    /**
     * @var array Hidden fields from array/json access
     */
    protected $hidden = ['followable_id', 'followable_type'];

    /**
     * @var array Relations
     */
    public $morphTo = ['followable'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    /**
     * Which events to record for the auth'd user.
     *
     * @var array
     */
    protected static $recordEvents = ['created'];

    public static function toggle($user, $followable)
    {
        if (static::check($user, $followable)) {
            static::unfollow($user, $followable);
            return true;
        }
        else {
            static::follow($user, $followable);
            return false;
        }
    }

    public static function check($user, $followable)
    {
        return self::where('user_id', $user->id)
            ->where('followable_id', $followable->id)
            ->where('followable_type', get_class($followable))->count() > 0;
    }


    public static function follow($user, $followable)
    {
        $obj = self::where('user_id', $user->id)
            ->where('followable_id', $followable->id)
            ->where('followable_type', get_class($followable))->first();

        if (!$obj) {

            $follow = new self;
            $follow->user_id = $user->id;
            $follow->followable_id = $followable->id;
            $follow->followable_type = get_class($followable);
            $follow->save();

            /*
             * Extensibility
             */
            Event::fire('social.follow', [$follow, $followable]);
        }

    }

    public static function unfollow($user, $followable)
    {
        $follow = self::where('user_id', $user->id)
            ->where('followable_id', $followable->id)
            ->where('followable_type', get_class($followable))
            ->first();

        $follow->delete();

        /*
         * Extensibility
         */
        Event::fire('social.unfollow', [$follow, $followable]);
    }

    public function getFollowableAttribute()
    {
        return call_user_func([$this->followable_type, 'find'], $this->followable_id);
    }

} 