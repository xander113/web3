<?php
namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Events\ForumReplyPosted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function index()
    {
        $categories = ForumCategory::with(['forums' => fn($q) => $q->withCount('topics')])->orderBy('sort_order')->get();
        return Inertia::render('Forum/Index', ['categories' => $categories]);
    }

    public function showForum(string $id)
    {
        $forum = Forum::findOrFail($id);
        $topics = ForumTopic::where('forum_id', $id)
            ->with('user:id,username')
            ->orderByDesc('is_pinned')->orderByDesc('last_activity_at')
            ->paginate(20);
        return Inertia::render('Forum/Forum', ['forum' => $forum, 'topics' => $topics]);
    }

    public function showTopic(string $id)
    {
        $topic = ForumTopic::with('user:id,username,rank,post_count,created_at')->findOrFail($id);
        $replies = ForumReply::where('topic_id', $id)
            ->with('user:id,username,rank,post_count,created_at')
            ->orderBy('created_at')
            ->paginate(20);
        return Inertia::render('Forum/Topic', ['topic' => $topic, 'replies' => $replies]);
    }

    public function createTopic(string $forumId)
    {
        $forum = Forum::findOrFail($forumId);
        return Inertia::render('Forum/CreateTopic', ['forum' => $forum]);
    }

    public function storeTopic(Request $request)
    {
        $request->validate([
            'forum_id' => ['required', 'exists:forums,id'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:10'],
        ]);
        $topic = ForumTopic::create([
            'forum_id' => $request->forum_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'last_activity_at' => now(),
        ]);
        Forum::find($request->forum_id)->increment('post_count');
        Auth::user()->increment('post_count');
        Auth::user()->update(['last_post_at' => now()]);
        return redirect()->route('forum.topic.show', $topic->id);
    }

    public function storeReply(Request $request, string $topicId)
    {
        $request->validate(['body' => ['required', 'string', 'min:3']]);
        $topic = ForumTopic::findOrFail($topicId);
        if ($topic->is_locked && !Auth::user()->isModerator()) {
            return back()->withErrors(['error' => 'Topic is locked.']);
        }
        $reply = ForumReply::create([
            'topic_id' => $topicId,
            'forum_id' => $topic->forum_id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);
        $topic->increment('reply_count');
        $topic->update(['last_activity_at' => now()]);
        Auth::user()->increment('post_count');
        Auth::user()->update(['last_post_at' => now()]);
        broadcast(new ForumReplyPosted($reply->load('user')))->toOthers();
        return back()->with('success', 'Reply posted.');
    }

    public function deleteTopic(string $id)
    {
        $topic = ForumTopic::findOrFail($id);
        if ($topic->user_id !== Auth::id() && !Auth::user()->isModerator()) abort(403);
        $topic->replies()->delete();
        $topic->delete();
        return redirect()->route('forum.index')->with('success', 'Topic deleted.');
    }

    public function deleteReply(string $id)
    {
        $reply = ForumReply::findOrFail($id);
        if ($reply->user_id !== Auth::id() && !Auth::user()->isModerator()) abort(403);
        $reply->delete();
        $reply->topic->decrement('reply_count');
        return back()->with('success', 'Reply deleted.');
    }
}
