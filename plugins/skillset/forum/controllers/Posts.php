<?php namespace Skillset\Forum\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\User;
use Skillset\Forum\Models\Post;

class Posts extends Controller
{
    use ApiResponser;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\RelationController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Skillset.Forum', 'main-menu-item', 'side-menu-item');
    }

    public function get(Request $request, Post $post)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'keyword' => 'sometimes|string',
            'sort' => 'sometimes|array',
            'sort.parameter' => 'required_with:sort|string',
            'sort.direction' => 'required_with:sort|string',
        ];

        $data = $request->only(array_keys($rules));

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $validatedParams = $request->validate($rules);

        $user = User::find(config('auth.UserID'));
        $user->update(['last_seen_forum' => now()]);

        return $this->response([
            'posts' => $post->getData($validatedParams),
        ]);
    }

    public function store(Request $request)
    {
        $rules =  [
            'description' => 'required|string',
            'images' => 'nullable|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg|max:10000',
            'video' => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime',
        ];

        $data = $request->only(array_keys($rules));

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $validatedData = $request->validate($rules);

        $validatedData['user_id'] = config('auth.UserID');

        $post = new Post($validatedData);

        $post->video = $request->file('video') ?? '';

        $post->images = $validatedData['images'] ?? [];

        $post->save();

        return $this->successResponse([]);
    }

    public function show($lang, $id)
    {
        $post = Post::with(['user', 'video', 'images', 'likes'])
            ->withCount('comments', 'likes')
            ->where('id', $id)
            ->first();

        if(!$post){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $user = (new User)->filterInfo($post->user);

        $post = $post->toArray();

        $post['user'] = $user;

        $post['liked'] = $post['likes_count'] &&
            !!collect($post['likes'])->firstWhere('user_id',
                config('auth.UserID')
            );

        return $this->response([
            'post' => $post,
        ]);
    }

    public function destroy($lang, $id)
    {
        $post = Post::find($id);

        if(!$post){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        if ($post->user_id != config('auth.UserID')){
            return $this->errorResponse('Forbidden', 403);
        }

        $post->delete();

        return $this->successResponse([]);
    }

    public function like(Request $request)
    {
        $authUserId = config('auth.UserID');

        $validatedData = $request->validate([
            'post_id' => 'required|exists:skillset_forum_posts,id',
            'like' => 'required|bool',
        ]);

        $post = Post::find($validatedData['post_id']);

        if ($validatedData['like']){
            $post->likes()->firstOrCreate([
                'user_id' => $authUserId,
            ]);
        }else {
            $post->likes()->where('user_id', $authUserId)->delete();
        }

        return $this->successResponse([]);
    }
}
