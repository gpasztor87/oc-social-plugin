<?php namespace Autumn\Social\Models;

use Model;
use Event;

/**
 * Like model
 */
class Like extends Model
{
    use \Autumn\Social\Traits\RecordsActivity;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'user_likes';

    /**
     * @var array Hidden fields from array/json access
     */
    protected $hidden = ['likeable_id', 'likeable_type'];

    /**
     * @var array Relations
     */
    public $morphTo = ['likeable'];

    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    /**
     * Which events to record for the auth'd user.
     *
     * @var array
     */
    protected static $recordEvents = ['created'];

    public static function toggle($user, $likeable)
    {
        if (static::check($user, $likeable)) {
            static::dislike($user, $likeable);
            return true;
        }
        else {
            static::like($user, $likeable);
            return false;
        }
    }

    public static function check($user, $likeable)
    {
        return self::where('user_id', $user->id)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->count() > 0;
    }

    public static function like($user, $likeable)
    {
        $obj = self::where('user_id', $user->id)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))->first();

        if (!$obj) {

            $like = new self;
            $like->user_id = $user->id;
            $like->likeable_id = $likeable->id;
            $like->likeable_type = get_class($likeable);
            $like->save();

            /*
             * Extensibility
             */
            Event::fire('social.like', [$like, $likeable]);
        }

    }

    public static function dislike($user, $likeable)
    {
        $like = self::where('user_id', $user->id)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->first();

        $like->delete();

        /*
         * Extensibility
         */
        Event::fire('social.dislike', [$like, $likeable]);
    }

    public function getLikeableAttribute()
    {
        return call_user_func([$this->likeable_type, 'find'], $this->likeable_id);
    }

} 