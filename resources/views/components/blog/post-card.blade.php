<div {{ $attributes->merge(['class' => 'flex flex-col rounded-2xl shadow-xl overflow-hidden transition hover:scale-103']) }}>

    @if (!empty($post->getFirstMediaUrl('blog-images')))
        <a href="{{route('blog.view', $post->slug)}}">
            <img src="{{$post->getFirstMediaUrl('blog-images')}}" alt="{{$post->title}}" class="h-52 object-cover w-full">
        </a>
    @else
        <div class="h-52 bg-gradient-to-r from-primary-300 to-primary-700"></div>
    @endif
    <div class="flex flex-col flex-wrap gap-1 mb-6 flex-grow align-items-stretch p-4 mt-1">
        <a href="{{route('blog.view', $post->slug)}}" class="text-primary-900">
            <x-heading.h6 class="">{{ $post->title }}</x-heading.h6>
        </a>
        <div class="text-neutral-400 text-xs">
            @if($post->is_published)
                {{ date(config('app.date_format'), strtotime($post->published_at)) }}
            @else
                [{{ __('Draft') }}]
            @endif
            —
            {{ $post->author->getPublicName() }}
        </div>
        @if ($post->blogPostCategory()->exists())
            <div class="text-neutral-400 rounded-lg px-2 py-1 border border-neutral-300 max-w-fit text-xs mt-3 hover:bg-neutral-100">
                <a href="{{route('blog.category', ['slug' => $post->blogPostCategory->slug])}}">{{ $post->blogPostCategory->name }}</a>
            </div>
        @endif
    </div>

    <div class="flex justify-end content-end pb-4 pr-4 text-xs font-light">
        <a href="{{route('blog.view', $post->slug)}}">{{ __('Read more >') }}</a>
    </div>
</div>
