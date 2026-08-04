@extends('layouts.settings')

@section('title', 'Configuración')
@section('settings-index', 'true')

@section('content')
    <div class="settings-layout__landing">
        <div class="settings-layout__mobile-nav">
            @include('partials.settings-nav')
        </div>
        <div class="settings-layout__welcome">
            @include('configuracion.cuenta.partials.perfil')
        </div>
    </div>
@endsection
