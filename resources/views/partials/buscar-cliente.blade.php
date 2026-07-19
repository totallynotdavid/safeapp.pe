{{--
    The RUC resolution step every client-scoped workflow opens with.

    A GET form pointed back at the screen that included it, so the resolved
    state is a URL the agent can reload or share. Legacy gave each workflow its
    own RUC box wired to its own AJAX endpoint, which is why four screens
    disagreed about what a RUC resolves to.

    Expects: $ruta (route name to submit to), $ruc (current value or null).
--}}
<form method="GET" action="{{ route($ruta) }}" class="rounded-lg border border-gray-200 bg-white p-6">
    <label for="ruc" class="block text-sm font-medium text-gray-700">RUC del cliente</label>
    <div class="mt-1 flex gap-2">
        <input id="ruc" name="ruc" inputmode="numeric" maxlength="11" value="{{ $ruc }}" required
               class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Buscar</button>
    </div>
</form>
