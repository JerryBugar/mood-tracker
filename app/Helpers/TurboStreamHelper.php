<?php

namespace App\Helpers;

/**
 * TurboStreamHelper adalah kelas bantuan untuk membuat Turbo Streams.
 * Kelas ini menyediakan metode-metode untuk membuat berbagai jenis stream
 * yang digunakan dalam Turbo Laravel untuk pembaruan halaman tanpa refresh.
 */
class TurboStreamHelper
{
    /**
     * Membuat turbo stream untuk mengganti konten elemen.
     *
     * @param string $target ID elemen target
     * @param string $content Konten baru
     * @param string $templateWrapper Wrapper untuk konten (default: 'div')
     * @return string String berisi turbo stream untuk mengganti elemen
     */
    public static function replace(string $target, string $content, string $templateWrapper = 'div'): string
    {
        return self::buildStream('replace', $target, $content, $templateWrapper);
    }

    /**
     * Membuat turbo stream untuk menambahkan konten ke awal elemen.
     *
     * @param string $target ID elemen target
     * @param string $content Konten baru
     * @param string $templateWrapper Wrapper untuk konten (default: 'div')
     * @return string String berisi turbo stream untuk menambahkan ke awal elemen
     */
    public static function prepend(string $target, string $content, string $templateWrapper = 'div'): string
    {
        return self::buildStream('prepend', $target, $content, $templateWrapper);
    }

    /**
     * Membuat turbo stream untuk menambahkan konten ke akhir elemen.
     *
     * @param string $target ID elemen target
     * @param string $content Konten baru
     * @param string $templateWrapper Wrapper untuk konten (default: 'div')
     * @return string String berisi turbo stream untuk menambahkan ke akhir elemen
     */
    public static function append(string $target, string $content, string $templateWrapper = 'div'): string
    {
        return self::buildStream('append', $target, $content, $templateWrapper);
    }

    /**
     * Membuat turbo stream untuk menghapus elemen.
     *
     * @param string $target ID elemen target
     * @return string String berisi turbo stream untuk menghapus elemen
     */
    public static function remove(string $target): string
    {
        return self::buildStream('remove', $target, '');
    }

    /**
     * Membuat turbo stream untuk mengganti atribut elemen.
     *
     * @param string $target ID elemen target
     * @param string $attribute Atribut yang akan diganti
     * @param string $value Nilai baru untuk atribut
     * @return string String berisi turbo stream untuk mengganti atribut elemen
     */
    public static function updateAttribute(string $target, string $attribute, string $value): string
    {
        $content = "<template data-turbo-stream=\"true\"></template>";
        $stream = '<turbo-stream action="update_attribute" target="' . $target . '" attribute="' . $attribute . '" value="' . $value . '">';
        $stream .= $content;
        $stream .= '</turbo-stream>';
        return $stream;
    }

    /**
     * Membangun turbo stream dengan action dan konten yang ditentukan.
     *
     * @param string $action Jenis aksi turbo stream (replace, prepend, append, remove)
     * @param string $target ID elemen target
     * @param string $content Konten untuk disisipkan (kosong untuk remove)
     * @param string $templateWrapper Wrapper untuk konten (default: 'div')
     * @return string String berisi turbo stream yang dibangun
     */
    private static function buildStream(string $action, string $target, string $content, string $templateWrapper = 'div'): string
    {
        $stream = '<turbo-stream action="' . $action . '" target="' . $target . '">';
        $stream .= '<template>';

        if ($action !== 'remove') {
            // Untuk 'replace', konten digantikan secara langsung 
            if ($action === 'replace') {
                $stream .= $content;
            } else {
                $stream .= '<' . $templateWrapper . ' id="' . $target . '">' . $content . '</' . $templateWrapper . '>';
            }
        }

        $stream .= '</template>';
        $stream .= '</turbo-stream>';

        return $stream;
    }

    /**
     * Menggabungkan beberapa turbo stream menjadi satu.
     *
     * @param array $streams Array dari string turbo stream
     * @return string String berisi semua turbo stream yang digabungkan
     */
    public static function combine(array $streams): string
    {
        return implode('', $streams);
    }
}