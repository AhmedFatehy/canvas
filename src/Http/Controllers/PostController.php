<?php

namespace Canvas\Http\Controllers;

use Canvas\Models\Post;
use Canvas\Models\Tag;
use Canvas\Models\Topic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        if (request()->query('type') === 'draft') {
            $postBuilder = Post::forUser(request()->user())->draft();
        } else {
            $postBuilder = Post::forUser(request()->user())->published();
        }

        return response()->json([
            'posts' => $postBuilder->latest()->withCount('views')->paginate(),
            'draftCount' => Post::forUser(request()->user())->draft()->count(),
            'publishedCount' => Post::forUser(request()->user())->published()->count(),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function show($id): JsonResponse
    {
        $tags = Tag::get(['name', 'slug']);
        $topics = Topic::get(['name', 'slug']);

        if (Post::forUser(request()->user())->pluck('id')->contains($id)) {
            return response()->json([
                'post' => Post::forUser(request()->user())->with('tags:name,slug', 'topic:name,slug')->find($id),
                'tags' => $tags,
                'topics' => $topics,
            ]);
        } elseif ($id === 'create') {
            $uuid = Uuid::uuid4();

            return response()->json([
                'post' => Post::make([
                    'id' => $uuid->toString(),
                    'slug' => "post-{$uuid->toString()}",
                ]),
                'tags' => $tags,
                'topics' => $topics,
            ]);
        } else {
            return response()->json(null, 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function store($id): JsonResponse
    {
        $post = Post::forUser(request()->user())->find($id) ?? new Post(['id' => $id]);

        $data = [
            'id' => $id,
            'slug' => request('slug', $post->slug),
            'title' => request('title', __('canvas::app.title', [], optional($post->userMeta)->locale)),
            'summary' => request('summary', $post->summary),
            'body' => request('body', $post->body),
            'published_at' => request('published_at', $post->published_at),
            'featured_image' => request('featured_image', $post->featured_image),
            'featured_image_caption' => request('featured_image_caption', $post->featured_image_caption),
            'meta' => [
                'title' => request('meta.title', optional($post->meta)['title']),
                'description' => request('meta.description', optional($post->meta)['description']),
                'canonical_link' => request('meta.canonical_link', optional($post->meta)['canonical_link']),
            ],
            'user_id' => request()->user()->id,
        ];

        $rules = [
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('canvas_posts')->where(function ($query) {
                    return $query->where('slug', request('slug'))->where('user_id', request()->user()->id);
                })->ignore($id)->whereNull('deleted_at'),
            ],
        ];

        $messages = [
            'required' => __('canvas::app.validation_required', [], optional($post->userMeta)->locale),
            'unique' => __('canvas::app.validation_unique', [], optional($post->userMeta)->locale),
        ];

        validator($data, $rules, $messages)->validate();

        $post->fill($data);

        $post->save();

        $post->topic()->sync($this->syncTopic(request('topic', [])));

        $post->tags()->sync($this->syncTags(request('tags', [])));

        return response()->json($post->refresh(), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $post = Post::forUser(request()->user())->findOrFail($id);

        $post->delete();

        return response()->json(null, 204);
    }

    /**
     * Sync the topic assigned to the post.
     *
     * @param $incomingTopic
     * @return array
     * @throws Exception
     */
    private function syncTopic($incomingTopic): array
    {
        if (collect($incomingTopic)->isEmpty()) {
            return [];
        }

        $topic = Topic::firstWhere('slug', $incomingTopic['slug']);

        if (! $topic) {
            $topic = Topic::create([
                'id' => $id = Uuid::uuid4()->toString(),
                'name' => $incomingTopic['name'],
                'slug' => $incomingTopic['slug'],
                'user_id' => request()->user()->id,
            ]);
        }

        return collect((string) $topic->id)->toArray();
    }

    /**
     * Sync the tags assigned to the post.
     *
     * @param $incomingTags
     * @return array
     */
    private function syncTags($incomingTags): array
    {
        if (collect($incomingTags)->isEmpty()) {
            return [];
        }

        $tags = Tag::get(['id', 'name', 'slug']);

        return collect($incomingTags)->map(function ($incomingTag) use ($tags) {
            $tag = $tags->firstWhere('slug', $incomingTag['slug']);

            if (! $tag) {
                $tag = Tag::create([
                    'id' => $id = Uuid::uuid4()->toString(),
                    'name' => $incomingTag['name'],
                    'slug' => $incomingTag['slug'],
                    'user_id' => request()->user()->id,
                ]);
            }

            return (string) $tag->id;
        })->toArray();
    }
}
