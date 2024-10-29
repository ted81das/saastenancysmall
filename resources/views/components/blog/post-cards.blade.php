@props(['posts', 'linkToMorePosts' => false])

@if(count($posts) > 0)

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mx-auto max-w-none md:max-w-6xl p-4 mt-6">
        @foreach($posts as $post)
            <x-blog.post-card :post="$post" />
        @endforeach
    </div>

    @if($linkToMorePosts)
        <div class="text-center mt-6">
            <x-button-link.primary-outline href="{{route('blog')}}">
                {{ __('More posts') }}
            </x-button-link.primary-outline>
        </div>
    @endif

@endif
