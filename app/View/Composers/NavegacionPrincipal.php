<?php

namespace App\View\Composers;

use App\Models\Dato;
use App\Navigation\RutaDeGestion;
use App\Navigation\Sidebar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavegacionPrincipal
{
    public function __construct(
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $usuario = $this->request->user();

        $view->with([
            'sidebarSections' => $usuario ? Sidebar::forUser($usuario, $this->rutaResaltada()) : collect(),
            'settingsUrl' => $usuario ? Sidebar::settingsUrlForUser($usuario) : null,
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
