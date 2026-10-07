<?php

namespace App\View\Composers;

use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavegacionDeConfiguracion
{
    public function __construct(
        private readonly Request $request,
        #[CurrentUser]
        private readonly ?Usuario $usuario,
    ) {}

    public function compose(View $view): void
    {
        $view->with(
            'settingsSections',
            $this->usuario ? Sidebar::settingsForUser($this->usuario, $this->rutaResaltada()) : collect(),
        );
    }

    /**
     * Configuración's landing page has no nav item. It opens on the profile
     * pane, so that is the item to mark.
     */
    private function rutaResaltada(): ?string
    {
        $ruta = $this->request->route()?->getName();

        return $ruta === 'configuracion.index' ? 'configuracion.perfil' : $ruta;
    }
}
