<?php

namespace Database\Factories;

use App\Models\Sesi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sesi>
 */
class SesiFactory extends Factory
{
    protected $model = Sesi::class;

    public function definition(): array
    {
        $waktuFoto = now()->subDays(fake()->numberBetween(0, 30));

        return [
            // id selalu dibuat di sisi klien (UUID v4) sesuai kontrak API.
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'waktu_foto' => $waktuFoto,
            't0' => (clone $waktuFoto)->addMinutes(20),
            'status' => 'selesai',
            'waktu_tidak_pasti' => false,
        ];
    }
}
