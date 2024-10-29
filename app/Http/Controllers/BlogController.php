<?php

namespace App\Http\Controllers;

use App\Models\BlogPostCategory;
use App\Services\BlogManager;

class BlogController extends Controller
{
    public function __construct(
        private BlogManager $blogManager
    ) {

    }

    public function view(string $slug)
    {
        $user = auth()->user();
        $isPublished = $user && $user->isAdmin() ? null : true; // if user is admin, show all posts, otherwise only published posts

        $post = $this->blogManager->getBlogBySlug($slug, $isPublished);

        return view('blog.view', [
            'post' => $post,
            'morePosts' => $this->blogManager->getMorePosts($post),
        ]);
    }

    public function all()
    {
        return view('blog.all', [
            'posts' => $this->blogManager->getAllPosts(),
        ]);
    }

    public function category(string $slug)
    {
        $category = BlogPostCategory::where('slug', $slug)->firstOrFail();
        return view('blog.category', [
            'category' => $category,
            'posts' => $this->blogManager->getAllPostsForCategory($category),
        ]);
    }
}
