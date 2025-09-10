<ul class="pagination justify-content-end">
    <!-- Previous Page Link -->
    @if ($paginator->onFirstPage())
    <li aria-disabled="true" aria-label="Previous" class="page-item disabled">
        <a aria-hidden="true" class="page-link" href="#">
            <i class="far fa-angle-left page-arrow align-middle me-2">
            </i>
            <span class="align-middle">
                Prev
            </span>
        </a>
    </li>
    @else
    <li class="page-item">
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
            <i class="far fa-angle-left page-arrow align-middle me-2">
            </i>
            <span class="align-middle">
                Prev
            </span>
        </a>
    </li>
    @endif
    <!-- Pagination Elements -->
    @foreach ($elements as $element)
    <!-- "Three Dots" Separator -->
    @if (is_string($element))
    <li class="page-item disabled">
        <span class="page-link">
            {{ $element }}
        </span>
    </li>
    @endif
    <!-- Array Of Links -->
    @if (is_array($element))
        @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
    <li aria-current="page" class="page-item active">
        <span class="page-link">
            {{ $page }}
        </span>
    </li>
    @else
    <li class="page-item">
        <a class="page-link" href="{{ $url }}">
            {{ $page }}
        </a>
    </li>
    @endif
        @endforeach
    @endif
@endforeach
    <!-- Next Page Link -->
    @if ($paginator->hasMorePages())
    <li class="page-item">
        <a aria-label="Next" class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
            <span class="align-middle">
                Next
            </span>
            <i class="far fa-angle-right page-arrow align-middle ms-2">
            </i>
        </a>
    </li>
    @else
    <li aria-disabled="true" class="page-item disabled">
        <a aria-hidden="true" class="page-link" href="#">
            <span class="align-middle">
                Next
            </span>
            <i class="far fa-angle-right page-arrow align-middle ms-2">
            </i>
        </a>
    </li>
    @endif
</ul>