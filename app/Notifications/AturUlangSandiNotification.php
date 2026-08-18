<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Menggantikan notifikasi reset-sandi bawaan Laravel, yang membuat tautan ke
 * halaman web /password/reset/{token} — tidak relevan di sini karena tidak
 * ada klien web. Email ini cukup memuat token & email mentah supaya
 * aplikasi mobile bisa memakainya langsung ke POST /auth/atur-ulang-sandi.
 */
class AturUlangSandiNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi AsaWatch')
            ->line('Kami menerima permintaan atur ulang kata sandi untuk akun AsaWatch kamu.')
            ->line('Buka aplikasi AsaWatch, lalu masukkan kode berikut pada layar atur ulang sandi:')
            ->line('**'.$this->token.'**')
            ->line('Kode ini berlaku selama 60 menit. Abaikan email ini jika kamu tidak meminta atur ulang sandi.');
    }
}
