const SAVE_ERROR = "No se pudo guardar la preferencia.";

for (const form of document.querySelectorAll("[data-preference-form]")) {
    if (!(form instanceof HTMLFormElement)) continue;

    for (const input of form.querySelectorAll(".switch__input")) {
        if (!(input instanceof HTMLInputElement)) continue;

        input.addEventListener("change", async () => {
            const previousChecked = !input.checked;
            const switchElement = input.closest("[data-switch]");

            switchElement?.setAttribute("data-loading", "true");
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

                if (!response.ok) throw new Error(SAVE_ERROR);
            } catch (error) {
                input.checked = previousChecked;
                window.alert(error instanceof Error ? error.message : SAVE_ERROR);
            } finally {
                input.disabled = false;
                switchElement?.removeAttribute("data-loading");
            }
        });
    }
}
