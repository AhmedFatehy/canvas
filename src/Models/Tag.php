<?php

namespace Canvas\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;

class Tag extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'canvas_tags';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * Get the posts relationship.
     *
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'canvas_posts_tags', 'tag_id', 'post_id');
    }

    /**
     * Get the user relationship.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('canvas.user', User::class));
    }

    /**
     * Get the user meta relationship.
     *
     * @return HasOneThrough
     */
    public function userMeta(): HasOneThrough
    {
        return $this->hasOneThrough(
            UserMeta::class,
            config('canvas.user', User::class),
            'id',       // Foreign key on users table...
            'user_id',  // Foreign key on canvas_tags table...
            'user_id',  // Local key on canvas_tags table...
            'id'        // Local key on users table...
        );
    }

    /**
     * Scope a query to only include tags for a given user.
     *
     * @param $query
     * @param $user
     * @return Builder
     */
    public function scopeForUser($query, $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            $item->posts()->detach();
        });
    }
}
