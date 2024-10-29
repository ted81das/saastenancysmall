<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostCategory;
use Tests\Feature\FeatureTest;

class BlogControllerTest extends FeatureTest
{
    public function test_view()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();

        auth()->setUser($user);

        $post = BlogPost::create([
            'title' => 'Test Post 1',
            'slug' => 'test-post-1',
            'body' => 'Test content 1',
            'content' => 'Test content 1',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
            'author_id' => $user->id,
        ]);

        $response = $this->get(route('blog.view', $post->slug));

        $response->assertStatus(200);
        $response->assertSee($post->title);
    }

    public function test_all()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();

        auth()->setUser($user);

        $post = BlogPost::create([
            'title' => 'Test Post 2',
            'slug' => 'test-post-2',
            'body' => 'Test content 2',
            'content' => 'Test content 2',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
            'author_id' => $user->id,
        ]);

        $response = $this->get(route('blog'));

        $response->assertStatus(200);
        $response->assertSee($post->title);

    }

    public function test_category()
    {
        $this->withoutExceptionHandling();

        $user = $this->createAdminUser();

        auth()->setUser($user);

        $category = BlogPostCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $post = BlogPost::create([
            'title' => 'Test Post 3',
            'slug' => 'test-post-3',
            'body' => 'Test content 3',
            'content' => 'Test content 3',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
            'author_id' => $user->id,
            'blog_post_category_id' => $category->id,
        ]);

        $response = $this->get(route('blog.category', 'test-category'));

        $response->assertStatus(200);
        $response->assertSee($post->title);

    }
}
