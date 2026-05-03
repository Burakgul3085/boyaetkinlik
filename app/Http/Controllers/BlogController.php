<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        return view('frontend.blog.index', [
            'blogs' => Blog::query()->approved()->latest()->paginate(9),
        ]);
    }

    public function create()
    {
        return view('frontend.blog.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:400'],
            'content' => ['required', 'string', 'min:30'],
            'author_first_name' => ['required', 'string', 'max:100'],
            'author_last_name' => ['required', 'string', 'max:100'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
        ]);

        $payload = [
            'title' => $data['title'],
            'slug' => Blog::generateUniqueSlug($data['title']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
            'status' => 'pending',
        ];

        if ($request->hasFile('image_file')) {
            $payload['image_path'] = $request->file('image_file')->store('blog-images', 'public');
        }

        Blog::query()->create($payload);

        return redirect()->route('blog.create')->with('success', 'Blog yazınız gönderildi. Admin onayından sonra yayına alınacaktır.');
    }

    public function show(Blog $blog)
    {
        abort_if($blog->status !== 'approved', 404);

        return view('frontend.blog.show', [
            'blog' => $blog,
            'recentBlogs' => Blog::query()
                ->approved()
                ->whereKeyNot($blog->id)
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
