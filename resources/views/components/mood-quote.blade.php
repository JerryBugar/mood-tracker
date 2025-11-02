{{-- 
Komponen ini menampilkan kutipan motivasi
Props:
- quote: teks kutipan
- author: nama penulis kutipan
--}}
@props([
    'quote' => '',
    'author' => ''
])

<div class="mood-quote-container">
    <p id="moodQuote" class="mood-quote">{{ $quote }}</p>
    <small id="moodAuthor" class="mood-author">{{ $author }}</small>
</div>