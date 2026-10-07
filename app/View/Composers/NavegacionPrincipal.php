<?php

namespace App\View\Composers;

use App\Models\Dato;
use App\Models\Usuario;
use App\Navigation\RutaDeGestion;
use App\Navigation\Sidebar;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavegacionPrincipal
{
    public function __construct(
        private readonly Request $request,
        #[CurrentUser]
        private readonly ?Usuario $usuario,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'sidebarSections' => $this->usuario ? Sidebar::forUser($this->usuario, $this->rutaResaltada()) : collect(),
            'settingsUrl' => $this->usuario ? Sidebar::settingsUrlForUser($this->usuario) : null,
        ]);
    }

    /**
     * The route whose nav item should read as current. A gestión detail page
     * has no item of its own, so it borrows the one for its record's channel
     * and the operator keeps seeing where they came from.
     */
    private function rutaResaltada(): ?string
    {
        $ruta = $this->request->route()?->getName();
        $dato = $this->request->route('dato');

        if ($ruta === 'gestion.show' && $dato instanceof Dato) {
            return RutaDeGestion::index($dato->tipo_data);
        }

        return $ruta;
    }
}
