<?php

namespace App\Console\Commands;

use App\Models\BlogPostCategory;
use App\Services\BlogManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;

class GenerateSitemap extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a sitemap.xml file and add it to robots.txt';

    /**
     * Execute the console command.
     */
    public function handle(BlogManager $blogManager)
    {
        if (app()->environment('local')) {
            $this->warn('Not generating sitemap in local environment');

            return;
        }

        $routes = collect(Route::getRoutes()->getRoutes())->filter(function (\Illuminate\Routing\Route $route) {

            if (! in_array('GET', $route->methods)) {
                return false;
            }

            if (! isset($route->action['middleware']) || ! is_array($route->action['middleware'])) {
                return false;
            }

            if (in_array('sitemapped', $route->action['middleware'])) {
                return true;
            }

            return false;
        })->map(function ($route) {
            return route($route->getName());
        })->values()->toArray();

        // go through all blog posts and add them to the sitemap (chunked to avoid memory issues)

        $blogManager->getAllPostsQuery()->chunk(100, function ($posts) use (&$routes) {
            foreach ($posts as $post) {
                $routes[] = route('blog.view', $post->slug);
            }
        });

        // add all blog categories to the sitemap (that have posts)
        $categories = BlogPostCategory::whereHas('posts', function ($query) {
            $query->where('is_published', true);
        })->get();

        foreach ($categories as $category) {
            $routes[] = route('blog.category', $category->slug);
        }

        $sitemap = Sitemap::create();

        foreach ($routes as $route) {
            $sitemap->add($route);
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        // add the sitemap to robots.txt
        $robots = file_get_contents(public_path('robots.txt'));

        if (! str_contains($robots, 'Sitemap:')) {
            $robots .= "\n\nSitemap: ".url('sitemap.xml');
            file_put_contents(public_path('robots.txt'), $robots);
        }
    }
}
