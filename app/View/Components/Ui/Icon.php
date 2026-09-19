<?php

namespace App\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class Icon extends Component
{
    private const ICONS = [
        'arrow-down-tray' => 'heroicon-o-arrow-down-tray',
        'arrow-left' => 'heroicon-o-arrow-left',
        'arrow-uturn-left' => 'heroicon-o-arrow-uturn-left',
        'arrow-up-tray' => 'heroicon-o-arrow-up-tray',
        'banknotes' => 'heroicon-o-banknotes',
        'bell' => 'heroicon-o-bell',
        'bars-3' => 'heroicon-o-bars-3',
        'building-storefront' => 'heroicon-o-building-storefront',
        'calendar-days' => 'heroicon-o-calendar-days',
        'chart-bar' => 'heroicon-o-chart-bar',
        'check' => 'heroicon-o-check',
        'check-circle' => 'heroicon-o-check-circle',
        'chevron-down' => 'heroicon-o-chevron-down',
        'chevron-left' => 'heroicon-o-chevron-left',
        'chevron-right' => 'heroicon-o-chevron-right',
        'circle-stack' => 'heroicon-o-circle-stack',
        'cog-6-tooth' => 'heroicon-o-cog-6-tooth',
        'cube' => 'heroicon-o-cube',
        'device-phone-mobile' => 'heroicon-o-device-phone-mobile',
        'document-text' => 'heroicon-o-document-text',
        'exclamation-circle' => 'heroicon-o-exclamation-circle',
        'exclamation-triangle' => 'heroicon-o-exclamation-triangle',
        'home' => 'heroicon-o-home',
        'information-circle' => 'heroicon-o-information-circle',
        'key' => 'heroicon-o-key',
        'lock-closed' => 'heroicon-o-lock-closed',
        'magnifying-glass' => 'heroicon-o-magnifying-glass',
        'phone' => 'heroicon-o-phone',
        'pencil-square' => 'heroicon-o-pencil-square',
        'plus' => 'heroicon-o-plus',
        'power' => 'heroicon-o-power',
        'presentation-chart-line' => 'heroicon-o-presentation-chart-line',
        'shield-check' => 'heroicon-o-shield-check',
        'share' => 'heroicon-o-share',
        'user' => 'heroicon-o-user',
        'user-circle' => 'heroicon-o-user-circle',
        'user-group' => 'heroicon-o-user-group',
        'user-plus' => 'heroicon-o-user-plus',
        'users' => 'heroicon-o-users',
        'wifi' => 'heroicon-o-wifi',
        'wrench-screwdriver' => 'heroicon-o-wrench-screwdriver',
        'x-mark' => 'heroicon-o-x-mark',
    ];

    private const SIZES = ['xsm', 'sm', 'md', 'lg'];

    private const COLORS = [
        'primary',
        'secondary',
        'tertiary',
        'disabled',
        'accent',
        'success',
        'error',
        'warning',
        'inherit',
        'blue',
        'red',
        'green',
        'gray',
        'cyan',
        'teal',
        'yellow',
        'orange',
        'pink',
        'purple',
    ];

    public readonly string $component;

    public function __construct(
        public readonly string $name,
        public readonly string $size = 'md',
        public readonly string $color = 'inherit',
        public readonly ?string $label = null,
    ) {
        $this->component = self::ICONS[$name] ?? throw new InvalidArgumentException(
            "El icono «{$name}» no está registrado.",
        );

        if (! in_array($size, self::SIZES, true)) {
            throw new InvalidArgumentException("El tamaño de icono «{$size}» no existe.");
        }

        if (! in_array($color, self::COLORS, true)) {
            throw new InvalidArgumentException("El color de icono «{$color}» no existe.");
        }
    }

    public function render(): View
    {
        return view('components.ui.icon');
    }
}
