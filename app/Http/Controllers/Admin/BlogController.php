<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.blogs.index', [
            'pendingBlogs' => Blog::query()->where('status', 'pending')->latest()->get(),
            'approvedBlogs' => Blog::query()->where('status', 'approved')->latest()->get(),
            'rejectedBlogs' => Blog::query()->where('status', 'rejected')->latest()->get(),
        ]);
    }

    public function approve(Blog $blog): RedirectResponse
    {
        $blog->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Blog yazısı onaylandı ve yayına alındı.');
    }

    public function reject(Blog $blog): RedirectResponse
    {
        $blog->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('warning', 'Blog yazısı reddedildi.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($blog->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($blog->image_path);
        }

        $blog->delete();

        return back()->with('success', 'Blog yazısı silindi.');
    }
}
