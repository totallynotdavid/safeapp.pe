for (const form of document.querySelectorAll("[data-preference-form]")) {
    if (!(form instanceof HTMLFormElement)) continue;

    for (const input of form.querySelectorAll(".switch__input")) {
        if (!(input instanceof HTMLInputElement)) continue;

        input.addEventListener("change", async () => {
            const priorValue = !input.checked;
            const field = input.closest("[data-switch]");
            field?.setAttribute("data-loading", "true");
            input.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: new FormData(form),
                });

                if (!response.ok) throw new Error("No se pudo guardar la preferencia.");
            } catch (error) {
                input.checked = priorValue;
                window.alert(
                    error instanceof Error ? error.message : "No se pudo guardar la preferencia.",
                );
            } finally {
                input.disabled = false;
                field?.removeAttribute("data-loading");
            }
        });
    }
}
