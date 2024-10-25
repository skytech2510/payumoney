@if ($paginator->hasPages())
    <div class="mt-3">
        <nav class="pagi-nav f-13 gilroy-regular d-flex justify-content-between align-items-center"
            aria-label="...">
            <ul class="pagination mb-0 r-pagi">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <a class="page-link" href="javascript:void(0)">
                            {!! svgIcons('left_angle_sm') !!}
                            {{ __('Prev') }}
                        </a>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                            {!! svgIcons('left_angle_sm') !!}
                            {{ __('Prev') }}
                        </a>
                    </li>
                @endif

                @foreach ($elements as $element)

                    @if (is_string($element))
                        <li class="page-item disabled" aria-current="page">
                            <a class="page-link" href="javascript:void(0)">{{ $element }}</a>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">{{ $page }}</a></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                     @endif
                @endforeach


                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link dark-A0" href="{{ $paginator->nextPageUrl() }}">
                            {{ __('Next') }}
                            {!! svgIcons('right_angle_sm') !!}
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <a class="page-link dark-A0" href="javascript:void(0)"> 
                            {{ __('Next') }}
                            {!! svgIcons('right_angle_sm') !!}
                        </a>
                    </li>
                @endif
            </ul>
            <div>
                <p class="mb-0 text-gray-100 tran-title page-limite">{!! __('Showing') !!}: {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} {{  __('of') }} {{ $paginator->total()  }}</p>
            </div>
        </nav>
    </div>
@endif
