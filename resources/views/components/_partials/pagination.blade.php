@if($records->hasPages())
    <div class="d-flex justify-content-center mt-3">
        <nav aria-label="Navigasi halaman">
            <ul class="pagination" style="background-color: #ffffff; border-radius: 12px; padding: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                {{-- Tombol Previous --}}
                @if ($records->onFirstPage())
                    <li class="page-item disabled" style="display: inline-block; margin: 0 10px 0 0;">
                        <span class="page-link" style="border-radius: 8px; background-color: #f8f9fa; border: 1px solid #d98695; color: #d98695; cursor: not-allowed; padding: 8px 12px; text-decoration: none; display: block; transition: none;">Sebelumnya</span>
                    </li>
                @else
                    <li class="page-item" style="display: inline-block; margin: 0 10px 0 0;">
                        <a class="page-link" href="{{ route('home.pagination', ['page' => $records->currentPage() - 1]) }}" 
                           style="border-radius: 8px; background-color: #ffffff; border: 1px solid #d98695; color: #82272c; padding: 8px 12px; text-decoration: none; display: block;" 
                           rel="prev"
                           data-turbo-stream>Sebelumnya</a>
                    </li>
                @endif

                {{-- Nomor halaman --}}
                @foreach ($records->getUrlRange(1, $records->lastPage()) as $page => $url)
                    @if ($page == $records->currentPage())
                        <li class="page-item active" style="display: inline-block; margin: 0 2px;">
                            <span class="page-link" 
                                  style="border-radius: 8px; background-color: #82272c; border: 1px solid #82272c; color: #ffffff; padding: 8px 12px; display: block;">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item" style="display: inline-block; margin: 0 2px;">
                            <a class="page-link" href="{{ route('home.pagination', ['page' => $page]) }}" 
                               style="border-radius: 8px; background-color: #ffffff; border: 1px solid #d98695; color: #82272c; padding: 8px 12px; text-decoration: none; display: block;"
                               data-turbo-stream>{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Tombol Next --}}
                @if ($records->hasMorePages())
                    <li class="page-item" style="display: inline-block; margin: 0 0 0 10px;">
                        <a class="page-link" href="{{ route('home.pagination', ['page' => $records->currentPage() + 1]) }}" 
                           style="border-radius: 8px; background-color: #ffffff; border: 1px solid #d98695; color: #82272c; padding: 8px 12px; text-decoration: none; display: block;" 
                           rel="next"
                           data-turbo-stream>Berikutnya</a>
                    </li>
                @else
                    <li class="page-item disabled" style="display: inline-block; margin: 0 0 0 10px;">
                        <span class="page-link" style="border-radius: 8px; background-color: #f8f9fa; border: 1px solid #d98695; color: #d98695; cursor: not-allowed; padding: 8px 12px; text-decoration: none; display: block; transition: none;">Berikutnya</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif