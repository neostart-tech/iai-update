<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class WebCommunicationController extends Controller
{
    // Comments
    public function getComments(Request $request)
    {
        $query = BlogComment::with('blog:id,title')->orderBy('created_at', 'desc');
        if ($request->has('blog_id')) {
            $query->where('blog_id', $request->blog_id);
        }
        return response()->json($query->get());
    }

    public function updateCommentStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,rejected']);
        $comment = BlogComment::findOrFail($id);
        $comment->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Statut du commentaire mis à jour avec succès.',
            'comment' => $comment
        ]);
    }

    public function deleteComment($id)
    {
        $comment = BlogComment::findOrFail($id);
        $comment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Commentaire supprimé.'
        ]);
    }

    // Newsletter
    public function getNewsletterSubscribers()
    {
        $subscribers = NewsletterSubscriber::orderBy('created_at', 'desc')->get();
        return response()->json($subscribers);
    }

    public function deleteNewsletterSubscriber($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Abonné supprimé.'
        ]);
    }
}
