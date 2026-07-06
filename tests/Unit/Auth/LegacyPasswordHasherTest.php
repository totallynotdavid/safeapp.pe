<?php

namespace Tests\Unit\Auth;

use App\Models\Usuario;
use App\Services\Auth\LegacyPasswordHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Touches the DB because a successful legacy match rehashes and persists in
// place, so it lives under Feature-style RefreshDatabase rather than a pure
// unit test.
class LegacyPasswordHasherTest extends TestCase
{
    use RefreshDatabase;

    private LegacyPasswordHasher $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hasher = new LegacyPasswordHasher;
    }

    public function test_verifies_a_bcrypt_password_without_rehashing(): void
    {
        $usuario = Usuario::factory()->clave('correcta')->create();
        $original = $usuario->clave;

        $this->assertTrue($this->hasher->verifyAndUpgrade($usuario, 'correcta'));
        $this->assertSame($original, $usuario->fresh()->clave);
    }

    public function test_upgrades_a_legacy_md5_match_to_bcrypt(): void
    {
        $usuario = Usuario::factory()->claveMd5('correcta')->create();

        $this->assertTrue($this->hasher->verifyAndUpgrade($usuario, 'correcta'));

        $rehashed = $usuario->fresh()->clave;
        $this->assertNotSame(md5('correcta'), $rehashed);
        $this->assertTrue(password_verify('correcta', $rehashed));
    }

    public function test_rejects_a_wrong_password_against_bcrypt(): void
    {
        $usuario = Usuario::factory()->clave('correcta')->create();

        $this->assertFalse($this->hasher->verifyAndUpgrade($usuario, 'incorrecta'));
    }

    public function test_rejects_a_wrong_password_against_legacy_md5(): void
    {
        $usuario = Usuario::factory()->claveMd5('correcta')->create();

        $this->assertFalse($this->hasher->verifyAndUpgrade($usuario, 'incorrecta'));
        // A failed attempt must not disturb the stored hash.
        $this->assertSame(md5('correcta'), $usuario->fresh()->clave);
    }

    // A 32-char hex string that is not the MD5 of the input must not slip
    // through the legacy branch.
    public function test_rejects_a_lookalike_hex_string_that_is_not_the_md5(): void
    {
        $usuario = Usuario::factory()->state(['clave' => str_repeat('a', 32)])->create();

        $this->assertFalse($this->hasher->verifyAndUpgrade($usuario, 'cualquiera'));
    }
}
