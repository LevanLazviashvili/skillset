<?php namespace Skillset\Forum\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class Comment extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_forum_post_comments';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
    ];
    /**
     * @var array Relations
     */

    public $attachOne = [
        'video' => \System\Models\File::class
    ];

    public $attachMany = [
        'images'   =>   \System\Models\File::class,
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'post_id' => 'required',
        'user_id' => 'required',
        'comment' => 'nullable'
    ];

    public $belongsTo = [
        'user'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
        'post'       => [Post::class, 'key' => 'post_id', 'otherKey' => 'id'],
    ];

    public function getPostComments($params = [])
    {
        $query = self::with(['user', 'images', 'video', 'likes'])
            ->withCount('likes')
            ->where('post_id', Arr::get($params, 'post_id'))
            ->orderBy('id', 'desc');

        if ($sort = Arr::get($params,'sort')) {
            $query->orderBy('id', $sort['direction']);
        }else{
            $query->orderBy('id', 'desc');
        }

        $comments = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $comments->setCollection($comments->getCollection()->map(function ($item) use ($user) {
            $userInfo = $user->filterInfo($item->user);

            $item = $item->toArray();

            $item['user'] = $userInfo;
            $item['liked'] = $item['likes_count'] &&
                !!collect($item['likes'])->firstWhere('user_id',
                    config('auth.UserID')
                );

            unset($item['likes']);

            return $item;
        }));

        return $comments;
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
