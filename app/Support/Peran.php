<?php

namespace App\Support;

/**
 * Peran akun. Sebelumnya status admin ditentukan dengan mencocokkan alamat
 * email tertentu — artinya hanya boleh ada satu admin, dan mengganti alamat
 * email itu diam-diam mencabut aksesnya.
 */
enum Peran: string
{
    case ADMIN = 'admin';

    case RESPONDEN = 'responden';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::RESPONDEN => 'Responden',
        };
    }
}
