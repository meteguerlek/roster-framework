<nav aria-label="Page navigation example">
    <ul class="pagination">
        @if ($paginate->prev_page)
            <li class="page-item"><a class="page-link" href="{{ $paginate->uri.$paginate->prev_page }}">Previous</a></li>
        @endif

        @if ($paginate->multi_pager)
            @foreach($paginate->multi_pager as $multi_pager)
                <li class="page-item @if ($paginate->current_page == $multi_pager) active @endif">
                    <a class="page-link" href="{{ $paginate->uri.$multi_pager }}">{{ $multi_pager }}</a>
                </li>
            @endforeach
        @else
            @for ($i = 1; $i < $paginate->last_page + 1; $i++)
                <li class="page-item @if ($paginate->current_page == $i) active @endif">
                    <a class="page-link" href="{{ $paginate->uri.$i }}">{{ $i }}</a>
                </li>
            @endfor
        @endif

        @if ($paginate->next_page)
            <li class="page-item"><a class="page-link" href="{{ $paginate->uri.$paginate->next_page }}">Next</a></li>
        @endif
    </ul>
</nav>
