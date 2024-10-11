<?php namespace Skillset\Forum\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_forum_posts';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'description',
    ];
    /**
     * @var array Relations
     */

    public $hasMany = [
        'postComments' => [Comment::class],
    ];

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
        'description' => 'required'
    ];

    public $belongsTo = [
        'user'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function getData($params = [])
    {
        $Query = self::with(['user', 'images', 'video', 'likes'])->withCount('comments', 'likes');

        if ($userId = Arr::get($params,'user_id')) {
            $Query->where('user_id', $userId);
        }

        if ($keyword = Arr::get($params, 'keyword')) {
            $Query->where('description', 'like', '%' . $keyword . '%')
                ->orWhereHas('user', function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('surname', 'like', '%' . $keyword . '%');
                });
        }

        $posts = $Query->orderBy('id', 'desc')->paginate($params['per_page'] ?? 10);

        $user = new User();

        $posts->setCollection($posts->getCollection()->map(function ($item) use ($user) {
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

        return $posts;
    }
}
