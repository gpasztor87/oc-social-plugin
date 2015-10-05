<?php namespace Autumn\Social\Models;

use Model;

/**
 * Like model
 */
class Like extends Model
{

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

    public static function check($user, $likeable) {
        return self::where('user_id', $user->id)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->count() > 0;
    }

    public function getLikeableAttribute()
    {
        return call_user_func([$this->likeable_type, 'find'], $this->likeable_id);
    }

} 