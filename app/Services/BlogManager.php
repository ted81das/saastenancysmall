<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostCategory;

class BlogManager
{
    public function getBlogBySlug(string $slug, ?bool $isPublished = true)
    {
        $post = BlogPost::where('slug', $slug);

        if ($isPublished) {
            $post->where('is_published', true);
        }

        return $post->firstOrFail();
    }

    public function getMorePosts(BlogPost $post, int $limit = 3)
    {
        return BlogPost::where('id', '!=', $post->id)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAllPosts(int $limit = 31)
    {
        return $this->getAllPostsQuery()
            ->paginate($limit);
    }

    public function getAllPostsForCategory(BlogPostCategory $category, int $limit = 31)
    {
        return $this->getAllPostsQuery()
            ->where('blog_post_category_id', $category->id)
            ->paginate($limit);
    }

    public function getAllPostsQuery()
    {
        return BlogPost::where('is_published', true)
            ->orderBy('published_at', 'desc');

    }
}
