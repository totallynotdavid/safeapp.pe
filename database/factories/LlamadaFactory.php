<?php

namespace Database\Factories;

use App\Models\Cliente;
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

    /**
     * Lets a fixture name the customer by RUC, resolving it the way the gestión
     * form does. Kept in step with DatoFactory so both read the same.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function newModel(array $attributes = []): Llamada
    {
        if (array_key_exists('ruc', $attributes)) {
            $attributes['id_cliente'] = Cliente::query()->firstOrCreate(
                ['ruc' => $attributes['ruc']],
                ['razon_social' => mb_strtoupper(fake()->company())],
            )->id_cliente;

            unset($attributes['ruc']);
        }

        return parent::newModel($attributes);
    }

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
            'id_cliente' => Cliente::factory(),
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
