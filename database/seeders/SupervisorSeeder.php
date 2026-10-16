<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only. No screen assigns a supervisor to a user, so this seeds a couple of
// supervisors and points the seeded users at one, giving the personnel screen
// real names to show instead of a column of dashes.
class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $supervisor = Supervisor::factory()->createOne(['nombre' => 'SUPERVISOR DEV']);
        Supervisor::factory()->createOne(['nombre' => 'SUPERVISOR BACKUP']);

        Usuario::query()->where('id_supervisor', 0)->update(['id_supervisor' => $supervisor->id_supervisor]);
    }
}
