<article class="blog-post mx-auto max-w-none md:max-w-3xl p-4 mt-6">
    <div class="flex flex-col flex-wrap gap-1 mb-6 flex-grow align-items-stretch">
        <x-heading.h1 class="text-primary-900 font-medium !text-4xl">{{ $post->title }}</x-heading.h1>
        <div class="text-neutral-500 text-sm mt-4">
            @if($post->is_published)
                {{ date(config('app.date_format'), strtotime($post->published_at)) }}
            @else
                [{{ __('Draft') }}]
            @endif
            —
            {{ $post->author->getPublicName() }}
            @if ($post->blogPostCategory()->exists())
                <span class="text-neutral-400 rounded-lg px-2 py-1 border border-neutral-300 max-w-fit text-xs ms-3 hover:bg-neutral-100">
                    <a href="{{route('blog.category', ['slug' => $post->blogPostCategory->slug])}}">{{ $post->blogPostCategory->name }}</a>
                </span>
            @endif
        </div>
    </div>


    @if ($post->media->count() > 0)
        <x-slot name="socialCard">
            {{ $post->getFirstMediaUrl('blog-images') }}
        </x-slot>

        <div class="mt-8">
            <img src="{{$post->getFirstMediaUrl('blog-images')}}" alt="{{$post->title}}" class="rounded-2xl	">
        </div>
    @endif

    <div class="pt-4">
        {!! $post->body !!}
    </div>

</article>
