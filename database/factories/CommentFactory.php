<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'content' => fake()->randomElement([
                'Terima kasih sudah menghubungi kami. Kami sudah menerima laporan Anda dan akan segera menindaklanjuti.',
                'Tim teknis kami sudah dikirimkan ke lokasi Anda. Mohon pastikan ada orang di rumah antara pukul 09.00-17.00.',
                'Kami sudah melakukan pengecekan dari sisi jaringan dan ditemukan gangguan pada node terdekat. Sedang dalam proses perbaikan.',
                'Apakah masalah sudah teratasi setelah dilakukan restart dari sisi kami? Mohon konfirmasi kondisi saat ini.',
                'Untuk mempercepat penanganan, mohon informasikan nomor pelanggan dan alamat lengkap Anda.',
                'Kami minta maaf atas ketidaknyamanan yang terjadi. Masalah sudah kami eskalasikan ke tim senior.',
                'Perbaikan telah selesai dilakukan. Silakan coba koneksi Anda sekarang dan informasikan apakah sudah normal.',
                'Berdasarkan pengecekan, tidak ada gangguan dari sisi jaringan. Kemungkinan masalah ada pada perangkat di rumah Anda.',
                'Kami sudah memeriksa catatan tagihan Anda. Biaya tersebut akan dikreditkan ke tagihan bulan berikutnya.',
                'Mohon maaf atas keterlambatan respons. Tim kami sedang menangani banyak laporan di area Anda saat ini.',
                'Koneksi Anda sudah berhasil dipulihkan dari sisi sistem. Silakan restart modem satu kali untuk menerapkan perubahan.',
                'Permintaan Anda sudah kami catat dan akan diproses dalam 1-3 hari kerja. Kami akan menghubungi Anda kembali.',
            ]),
            'is_internal_note' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(['is_internal_note' => true]);
    }
}
