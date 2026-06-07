<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Priority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $createdAt  = fake()->dateTimeBetween('-60 days', '-3 days');
        $status     = fake()->randomElement(['Open', 'Assigned', 'In Progress', 'Waiting for Customer', 'Resolved', 'Closed', 'Reopened', 'Escalated']);
        $resolvedAt = in_array($status, ['Resolved', 'Closed']) ? fake()->dateTimeBetween($createdAt, '-1 hour') : null;
        $closedAt   = $status === 'Closed' ? fake()->dateTimeBetween($resolvedAt, 'now') : null;

        return [
            'ticket_number' => 'TCK-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'created_at'       => $createdAt,
            'title' => fake()->randomElement([
                'Internet mati total sejak pagi', 'Lampu modem berkedip merah (LOS)',
                'Koneksi sangat lambat', 'Tidak bisa login ke aplikasi pelanggan',
                'Tagihan bulan ini tidak sesuai', 'Lupa password wifi',
                'Ingin upgrade kecepatan internet', 'Koneksi sering terputus tiba-tiba'
            ]),
            'description' => fake()->randomElement([
                'Koneksi internet di rumah saya mati total sejak pagi hari. Sudah saya coba restart modem beberapa kali namun tidak ada perubahan. Lampu LOS pada modem menyala merah terus-menerus. Mohon segera ditangani karena saya bekerja dari rumah.',
                'Kecepatan internet saya sangat lambat sejak kemarin malam. Hasil speedtest hanya menunjukkan 1-2 Mbps padahal paket saya seharusnya 50 Mbps. Sudah saya coba di beberapa perangkat dan hasilnya sama saja.',
                'Saya tidak bisa login ke aplikasi pelanggan menggunakan email dan password yang biasa saya gunakan. Muncul pesan error "kredensial tidak valid" padahal saya yakin passwordnya benar. Tolong bantu reset akses saya.',
                'Tagihan bulan ini tidak sesuai dengan paket yang saya gunakan. Saya berlangganan paket 100 Mbps seharga Rp 300.000, namun tagihan menunjukkan Rp 450.000 tanpa penjelasan. Mohon diperiksa dan dikoreksi.',
                'Koneksi internet saya sering terputus tiba-tiba setiap 30-60 menit sekali, kemudian menyambung kembali sendiri. Hal ini sangat mengganggu aktivitas kerja dan video call saya. Sudah berlangsung selama 3 hari terakhir.',
                'Saya ingin mengajukan upgrade paket dari 50 Mbps ke 100 Mbps. Mohon informasi mengenai biaya tambahan dan prosedur yang diperlukan. Jika memungkinkan saya ingin upgrade berlaku mulai bulan depan.',
                'Perangkat saya tidak bisa terhubung ke WiFi. Jaringan terdeteksi namun selalu gagal saat proses autentikasi. Perangkat lain di rumah bisa terhubung normal. Saya sudah coba lupa jaringan dan sambung ulang namun tetap gagal.',
                'Ada biaya tambahan di tagihan saya yang tidak saya kenali. Tercantum biaya "layanan premium" sebesar Rp 75.000 yang tidak pernah saya aktifkan. Mohon penjelasan dan pembatalan jika ini adalah kesalahan.',
                'Modem saya menunjukkan semua lampu indikator mati kecuali lampu power. Tidak ada koneksi sama sekali ke perangkat apapun. Sudah saya coba ganti kabel dan colokkan ke stop kontak yang berbeda namun kondisinya sama.',
                'Saya ingin mengajukan pemindahan layanan ke alamat baru karena saya akan pindah rumah bulan depan. Mohon informasi prosedur dan apakah ada biaya yang perlu disiapkan untuk proses pemindahan tersebut.',
            ]),
            'status'           => $status,
            'priority_id'      => Priority::factory(),
            'category_id'      => Category::factory(),
            'created_by'       => User::factory()->customer(),
            'assigned_agent_id' => null,
            'due_at'           => (clone $createdAt)->modify('+'.fake()->numberBetween(24, 120).' hours'),
            'response_due_at'  => (clone $createdAt)->modify('+'.fake()->numberBetween(1, 24).' hours'),
            'resolved_at'      => $resolvedAt,
            'closed_at'        => $closedAt,
        ];
    }

    public function assigned(?User $agent = null): static
    {
        return $this->state(fn () => [
            'status' => 'Assigned',
            'assigned_agent_id' => $agent?->id ?? User::factory()->agent(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => 'Resolved',
            'resolved_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_at' => now()->subHour(),
        ]);
    }
}
