<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogPost; 

class BlogPostController extends Controller
{
    // Show all blog posts
    public function index()
    {
        $posts = BlogPost::all();
        return view('blog.index', compact('posts'));
    }

    // Show individual post
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        return view('blog.show', compact('post'));
    }
}
