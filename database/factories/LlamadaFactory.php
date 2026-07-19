<?php

namespace Database\Factories;

use App\Models\Llamada;
use App\Models\Respuesta;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Llamada>
 */
class LlamadaFactory extends Factory
{
    protected $model = Llamada::class;

    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory(),
            // Point at a seeded catalog row when one exists, otherwise stand up a
            // single disposition so the factory works without RespuestaSeeder.
            'id_respuesta' => static fn (): int => (
                Respuesta::query()->value('id_respuesta')
                ?? Respuesta::create([
                    'descripcion' => 'Desea Movil',
                    'tipo' => 'LLAMADA',
                    'contactabilidad' => true,
                    'negociacion' => true,
                    'volverllamar' => false,
                ])->id_respuesta
            ),
            'fecha' => fake()->dateTimeBetween('-3 months', 'now'),
            'ruc' => fake()->numerify('20#########'),
            'razon_social' => mb_strtoupper(fake()->company()),
            'telefono' => fake()->numerify('9########'),
            'nombre_contacto' => mb_strtoupper(fake()->name()),
            'correo' => fake()->safeEmail(),
            'comentario' => fake()->sentence(),
            'gestion' => 'LLAMADA',
            'tipo_data' => fake()->randomElement(['MOVIL', 'FIJA', 'INHOUSE']),
            'q_movistar' => fake()->numberBetween(0, 10),
            'q_entel' => fake()->numberBetween(0, 10),
            'q_claro' => fake()->numberBetween(0, 10),
            'q_bitel' => fake()->numberBetween(0, 10),
        ];
    }
}
