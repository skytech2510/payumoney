<ol class="breadcrumb">
    @if(isset($breadcrumb))
        @foreach($breadcrumb as $b)
            <li class="{{ $loop->last ? '' : 'active' }}">
                @if(isset($b['icon']))
                    <i class="{{ $b['icon'] }}"></i>
                @endif
                @if(isset($b['href']))
                    <a href="{{ $b['href'] }}">
                        {{ $b['name'] }}
                    </a>
                @else
                    {{ $b['name'] }}
                @endif
            </li>
        @endforeach
    @endif
</ol>