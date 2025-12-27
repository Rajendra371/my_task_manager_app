@if ($paginator->hasPages())
    <div class="laravel-pagination">
        <nav>
          
            @if ($paginator->onFirstPage())
                <span class="disabled"><i class="bi bi-chevron-left"></i> Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Previous</a>
            @endif

            
            @foreach ($elements as $element)
                
                @if (is_string($element))
                    <span>{{ $element }}</span>
                @endif

               
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

           
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next <i class="bi bi-chevron-right"></i></a>
            @else
                <span class="disabled">Next <i class="bi bi-chevron-right"></i></span>
            @endif
        </nav>
    </div>
@endif