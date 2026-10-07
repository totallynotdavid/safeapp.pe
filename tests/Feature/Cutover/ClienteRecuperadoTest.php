<?php

namespace Tests\Feature\Cutover;

use App\Models\Cliente;
use App\Models\Dato;
use App\Models\Llamada;

class ClienteRecuperadoTest extends CutoverTestCase
{
    public function test_a_customer_known_only_from_a_contact_is_recovered(): void
    {
        // The 146,736-customer case. Legacy dropped the campaign row when the
        // list was replaced and the contact log was the only thing left holding
        // who this was.
        $this->filaLegado('llamada', [
            'ruc' => '20999888777',
            'razon_social' => 'SOLO EN LLAMADA SAC',
            'id_usuario' => 1,
            'id_respuesta' => 1,
            'fecha' => '2025-01-02 10:00:00',
        ]);

        $this->transformar();

        $cliente = Cliente::porRuc('20999888777');
        $this->assertNotNull($cliente);
        $this->assertSame('SOLO EN LLAMADA SAC', $cliente->razon_social);
        $this->assertSame(1, $cliente->llamadas()->count());
        // No campaign ever delivered them, which is exactly the state legacy
        // could not represent.
        $this->assertSame(0, $cliente->datos()->count());
    }

    public function test_one_customer_on_two_campaigns_is_one_identity(): void
    {
        $this->datoLegado(['origen' => 'CAMPAÑA JUNIO']);
        $this->datoLegado(['origen' => 'CAMPAÑA JULIO']);

        $this->transformar();

        $this->assertSame(1, Cliente::query()->count());
        $this->assertSame(2, Dato::query()->count());
    }

    public function test_the_campaign_file_names_the_customer_over_a_charge_note(): void
    {
        // Both tables carry a name for the same RUC. The case screens prefilled
        // theirs and then let a modal edit it, so the file wins.
        $this->datoLegado(['razon_social' => 'NOMBRE DE LA CAMPAÑA SAC']);
        $this->filaLegado('nc_movil', [
            'ruc' => '20123456789',
            'razon_social' => 'NOMBRE TIPEADO A MANO',
            'id_usuario' => 1,
        ]);

        $this->transformar();

        $this->assertSame('NOMBRE DE LA CAMPAÑA SAC', Cliente::porRuc('20123456789')?->razon_social);
    }

    public function test_a_ruc_that_is_not_a_number_is_not_a_customer(): void
    {
        // The export carries one: `?ruc`, named "razon_social", which is a CSV
        // header row that was imported as data.
        $this->datoLegado(['ruc' => '?ruc', 'razon_social' => 'razon_social']);
        $this->datoLegado();

        $this->transformar();

        $this->assertSame(1, Cliente::query()->count());
        $this->assertSame('20123456789', Cliente::query()->sole()->ruc);
        // The row it came on goes with it: a campaign record with no customer
        // has no subject.
        $this->assertSame(1, Dato::query()->count());
    }

    public function test_names_mangled_by_repeated_re_encoding_are_restored(): void
    {
        // Built by doing to the name exactly what legacy did: read the UTF-8
        // bytes as Latin-1 and encode them again, four times over. That is how
        // 1,359 values in the export are held.
        $mangled = 'PEÑA CONDE';

        for ($vuelta = 0; $vuelta < 4; $vuelta++) {
            $mangled = mb_convert_encoding($mangled, 'UTF-8', 'ISO-8859-1');
            $this->assertIsString($mangled);
        }

        $this->assertStringContainsString('Ã', $mangled);
        $this->datoLegado(['razon_social' => $mangled]);

        $this->transformar();

        $this->assertSame('PEÑA CONDE', Cliente::query()->sole()->razon_social);
    }

    public function test_a_contact_stays_attached_to_its_customer_and_not_to_a_campaign(): void
    {
        $this->datoLegado();
        $this->filaLegado('llamada', [
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'id_usuario' => 1,
            'id_respuesta' => 1,
            'fecha' => '2025-01-02 10:00:00',
        ]);

        $this->transformar();

        $llamada = Llamada::query()->sole();
        $this->assertSame(Cliente::porRuc('20123456789')?->id_cliente, $llamada->id_cliente);
        // Legacy had no reference to the campaign row, and re-deriving one by
        // joining on RUC and agent is ambiguous, so it stays unknown.
        $this->assertNull($llamada->id_dato);
    }
}
