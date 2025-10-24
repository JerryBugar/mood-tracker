<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MoodQuote;

class MoodQuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quotes = [
            [
                'quote' => 'Dibalik setiap kesulitan, tersimpan sebuah kesempatan.',
                'author' => 'Albert Einstein'
            ],
            [
                'quote' => 'Hidup ini seperti sebuah sepeda, agar tetap seimbang kita harus terus bergerak.',
                'author' => 'Albert Einstein'
            ],
            [
                'quote' => 'Cara terbaik untuk memprediksi masa depan adalah dengan menciptakannya.',
                'author' => 'Abraham Lincoln'
            ],
            [
                'quote' => 'Kesuksesan bukanlah kunci kebahagiaan. Kebahagiaan adalah kunci kesuksesan.',
                'author' => 'Albert Schweitzer'
            ],
            [
                'quote' => 'Orang yang melakukan hal yang mustahil, disebut seorang idealis.',
                'author' => 'Oscar Wilde'
            ],
            [
                'quote' => 'Hidup harus dirasakan, bukan sekadar dijalani.',
                'author' => 'Soekarno'
            ],
            [
                'quote' => 'Kegagalan adalah kesempatan untuk memulai lagi dengan lebih pandai.',
                'author' => 'Henry Ford'
            ],
            [
                'quote' => 'Jangan menunggu kesempatan, ciptakan kesempatan.',
                'author' => 'George Bernard Shaw'
            ],
            [
                'quote' => 'Yang tidak mungkin itu hanya masalah waktu.',
                'author' => 'Victor Hugo'
            ],
            [
                'quote' => 'Hidup adalah 10% apa yang terjadi pada kita dan 90% bagaimana kita meresponnya.',
                'author' => 'Charles R. Swindoll'
            ],
            [
                'quote' => 'Jangan menunggu kondisi yang ideal, mulailah dari kondisi saat ini.',
                'author' => 'Jim Rohn'
            ],
            [
                'quote' => 'Kesuksesan adalah perjalanan, bukan tujuan akhir.',
                'author' => 'Ben Sweetland'
            ],
            [
                'quote' => 'Percayalah bahwa hidup itu indah.',
                'author' => 'Bob Marley'
            ],
            [
                'quote' => 'Hidup adalah seni menggambar tanpa penghapus.',
                'author' => 'John Green'
            ],
            [
                'quote' => 'Jadilah perubahan yang ingin kamu lihat di dunia.',
                'author' => 'Mahatma Gandhi'
            ],
            [
                'quote' => 'Setiap detik dalam hidupmu adalah segar dan baru.',
                'author' => 'Og Mandino'
            ],
            [
                'quote' => 'Jangan memperbesar masalah, perbesarlah keyakinanmu.',
                'author' => 'Robert Collier'
            ],
            [
                'quote' => 'Hidup bukan tentang menemukan dirimu sendiri, tetapi menciptakan dirimu sendiri.',
                'author' => 'George Bernard Shaw'
            ],
            [
                'quote' => 'Kebahagiaan bukanlah sesuatu yang siap dibeli.',
                'author' => 'Helen Keller'
            ],
            [
                'quote' => 'Semua perubahan yang hebat dimulai dari ketidaknyamanan.',
                'author' => 'M. J. Ryan'
            ]
        ];

        foreach ($quotes as $quote) {
            \App\Models\MoodQuote::create([
                'quote' => $quote['quote'],
                'author' => $quote['author']
            ]);
        }
    }
}
