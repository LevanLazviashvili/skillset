<?php namespace Skillset\Forum\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\User;
use Skillset\Forum\Models\Comment;
use Skillset\Forum\Models\Post;
use skillset\Notifications\Models\Notification;

class Comments extends Controller
{
    use ApiResponser;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
    }

    public function get(Request $request, Comment $comment)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'post_id' => 'required|integer|min:1',
            'sort' => 'sometimes|array',
            'sort.direction' => 'sometimes|string|in:asc,desc',
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

        return $this->response([
            'comments' => $comment->getPostComments($validatedParams),
        ]);
    }

    public function store(Request $request)
    {
        $rules =  [
            'post_id' => 'required|exists:skillset_forum_posts,id',
            'comment' => 'required_without_all:images,video|string',
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

        $post = Post::find($validatedData['post_id']);

        $comment = new Comment($validatedData);

        $comment->video = $request->file('video') ?? '';

        $comment->images = $validatedData['images'] ?? [];

        $comment->save();

        (new Notification)->sendTemplateNotifications(
            $post->user_id,
            'forumNewComment',
            [],
            ['type' => 'forum_post_comment', 'id' => $post->id],
            'forum_post_details'
        );

        return $this->successResponse([]);
    }

    public function show($lang, $id)
    {
        $comment = Comment::with(['user', 'video', 'images', 'likes'])
            ->withCount('likes')
            ->where('id', $id)
            ->first();

        if(!$comment){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $comment->load(['user', 'video', 'images']);

        $user = (new User)->filterInfo($comment->user);

        $comment = $comment->toArray();

        $comment['user'] = $user;

        $comment['liked'] = $comment['likes_count'] &&
            !!collect($comment['likes'])->firstWhere('user_id',
                config('auth.UserID')
            );

        return $this->response([
            'comment' => $comment,
        ]);
    }

    public function destroy($lang, $id)
    {
        $comment = Comment::find($id);

        if(!$comment){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        if ($comment->user_id != config('auth.UserID')){
            return $this->errorResponse('Forbidden', 403);
        }

        $comment->delete();

        return $this->successResponse([]);
    }

    public function like(Request $request)
    {
        $authUserId = config('auth.UserID');

        $validatedData = $request->validate([
            'comment_id' => 'required|exists:skillset_forum_post_comments,id',
            'like' => 'required|bool',
        ]);

        $comment = Comment::find($validatedData['comment_id']);

        if ($validatedData['like']){
            $comment->likes()->firstOrCreate([
                'user_id' => $authUserId,
            ]);
        } else {
            $comment->likes()->where('user_id', $authUserId)->delete();
        }

        return $this->successResponse([]);
    }
}
