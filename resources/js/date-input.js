const pad = (value) => String(value).padStart(2, "0");

const toISO = (date) =>
    `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

const fromISO = (value) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value ?? "")) {
        return null;
    }

    const [year, month, day] = value.split("-").map(Number);
    const date = new Date(year, month - 1, day);

    return date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day
        ? date
        : null;
};

const formatInput = (date) =>
    date
        ? `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`
        : "";

const parseInput = (value) => {
    const trimmed = value.trim();
    const match = trimmed.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/);

    if (!match) {
        return fromISO(trimmed);
    }

    return fromISO(`${match[3]}-${pad(match[2])}-${pad(match[1])}`);
};

const dateLabel = new Intl.DateTimeFormat("es-PE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
});

const monthLabel = new Intl.DateTimeFormat("es-PE", {
    month: "long",
    year: "numeric",
});

const dayNames = ["D", "L", "M", "M", "J", "V", "S"];

const chevron = (direction) =>
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="${direction === "left" ? "M15.75 19.5 8.25 12l7.5-7.5" : "m8.25 4.5 7.5 7.5-7.5 7.5"}" /></svg>`;

for (const root of document.querySelectorAll("[data-date-input]")) {
    const input = root.querySelector(".date-input__control");
    const carrier = root.querySelector("[data-date-value]");
    const toggle = root.querySelector("[data-date-toggle]");
    const clear = root.querySelector("[data-date-clear]");
    const popover = root.querySelector("[data-date-popover]");
    const calendar = popover?.querySelector(".calendar");
    const alert = root.querySelector("[data-date-alert]");

    if (
        !(input instanceof HTMLInputElement) ||
        !(carrier instanceof HTMLInputElement) ||
        !(toggle instanceof HTMLButtonElement) ||
        !(popover instanceof HTMLElement) ||
        !(calendar instanceof HTMLElement) ||
        !(alert instanceof HTMLElement)
    ) {
        continue;
    }

    const min = fromISO(root.dataset.min);
    const max = fromISO(root.dataset.max);

    let selected = fromISO(carrier.value);

    const initial = selected ?? new Date();
    let visibleMonth = new Date(initial.getFullYear(), initial.getMonth(), 1);

    const isDisabled = (date) =>
        (min && date < min) || (max && date > max);

    const canNavigate = (delta) => {
        const target = new Date(
            visibleMonth.getFullYear(),
            visibleMonth.getMonth() + delta,
            1,
        );

        if (delta < 0 && min) {
            return (
                target.getFullYear() > min.getFullYear() ||
                (target.getFullYear() === min.getFullYear() &&
                    target.getMonth() >= min.getMonth())
            );
        }

        if (delta > 0 && max) {
            return (
                target.getFullYear() < max.getFullYear() ||
                (target.getFullYear() === max.getFullYear() &&
                    target.getMonth() <= max.getMonth())
            );
        }

        return true;
    };

    const setValue = (date) => {
        selected = date;

        const iso = date ? toISO(date) : "";

        carrier.value = iso;
        root.dataset.value = iso;
        input.value = formatInput(date);

        if (clear instanceof HTMLButtonElement) {
            clear.hidden = !date;
        }

        input.removeAttribute("aria-invalid");
        alert.textContent = "";

        carrier.dispatchEvent(new Event("change", { bubbles: true }));
    };

    const focusDay = (offset, boundary = null) => {
        const buttons = Array.from(
            calendar.querySelectorAll(".calendar__day:not(:disabled)"),
        );

        let index = buttons.indexOf(document.activeElement);

        if (boundary === "start") {
            index = 0;
        } else if (boundary === "end") {
            index = buttons.length - 1;
        } else {
            index = Math.max(
                0,
                Math.min(buttons.length - 1, index + offset),
            );
        }

        buttons[index]?.focus();
    };

    const renderHeader = () => {
        const header = document.createElement("div");
        header.className = "calendar__header";

        const previous = document.createElement("button");
        previous.type = "button";
        previous.className = "button ghost sm";
        previous.dataset.iconOnly = "true";
        previous.dataset.variant = "ghost";
        previous.dataset.size = "sm";
        previous.setAttribute("aria-label", "Mes anterior");
        previous.disabled = !canNavigate(-1);
        previous.innerHTML =
            `<span class="button__content"><span class="button__icon" aria-hidden="true">${chevron("left")}</span></span>`;

        const label = document.createElement("span");
        label.className = "calendar__month-label";
        label.textContent = monthLabel.format(visibleMonth);

        const next = document.createElement("button");
        next.type = "button";
        next.className = "button ghost sm";
        next.dataset.iconOnly = "true";
        next.dataset.variant = "ghost";
        next.dataset.size = "sm";
        next.setAttribute("aria-label", "Mes siguiente");
        next.disabled = !canNavigate(1);
        next.innerHTML =
            `<span class="button__content"><span class="button__icon" aria-hidden="true">${chevron("right")}</span></span>`;

        header.append(previous, label, next);

        return { header, previous, next, label };
    };

    const renderGrid = (label) => {
        const grid = document.createElement("div");
        grid.className = "calendar__grid";
        grid.setAttribute("role", "grid");
        grid.setAttribute("aria-label", label);

        const names = document.createElement("div");
        names.className = "calendar__row";
        names.setAttribute("role", "row");

        for (const name of dayNames) {
            const cell = document.createElement("div");
            cell.className = "calendar__day-name";
            cell.setAttribute("role", "columnheader");
            cell.textContent = name;
            names.append(cell);
        }

        grid.append(names);

        const first = new Date(
            visibleMonth.getFullYear(),
            visibleMonth.getMonth(),
            1,
        );
        const start = new Date(first);
        start.setDate(1 - first.getDay());

        const todayISO = toISO(new Date());
        const selectedISO = selected ? toISO(selected) : "";

        let seeded = false;

        for (let week = 0; week < 6; week += 1) {
            const row = document.createElement("div");
            row.className = "calendar__row";
            row.setAttribute("role", "row");

            for (let day = 0; day < 7; day += 1) {
                const date = new Date(start);
                date.setDate(start.getDate() + week * 7 + day);

                const iso = toISO(date);
                const outside = date.getMonth() !== visibleMonth.getMonth();
                const disabled = isDisabled(date);

                const cell = document.createElement("div");
                cell.className = "calendar__cell";
                cell.setAttribute("role", "gridcell");

                if (iso === selectedISO) {
                    cell.setAttribute("aria-selected", "true");
                }

                const button = document.createElement("button");
                button.type = "button";
                button.className = "calendar__day";
                button.dataset.date = iso;
                button.textContent = String(date.getDate());
                button.setAttribute("aria-label", dateLabel.format(date));
                button.disabled = disabled;

                const isInitialFocus =
                    !seeded &&
                    (iso === selectedISO ||
                        (!selectedISO && iso === todayISO));

                button.tabIndex = isInitialFocus ? 0 : -1;

                if (isInitialFocus) {
                    seeded = true;
                }

                if (outside) {
                    button.dataset.outside = "true";
                }

                if (iso === todayISO) {
                    button.setAttribute("aria-current", "date");
                }

                if (iso === selectedISO) {
                    button.dataset.selected = "true";
                }

                button.addEventListener("click", () => {
                    setValue(date);
                    popover.hidePopover();
                    input.focus({ preventScroll: true });
                });

                cell.append(button);
                row.append(cell);
            }

            grid.append(row);
        }

        if (!seeded) {
            const firstEnabled = grid.querySelector(
                ".calendar__day:not(:disabled)",
            );

            if (firstEnabled instanceof HTMLButtonElement) {
                firstEnabled.tabIndex = 0;
            }
        }

        return grid;
    };

    const render = () => {
        calendar.replaceChildren();

        const { header, previous, next, label } = renderHeader();
        const grid = renderGrid(label.textContent);

        calendar.append(header, grid);

        previous.addEventListener("click", () => {
            visibleMonth = new Date(
                visibleMonth.getFullYear(),
                visibleMonth.getMonth() - 1,
                1,
            );

            render();
        });

        next.addEventListener("click", () => {
            visibleMonth = new Date(
                visibleMonth.getFullYear(),
                visibleMonth.getMonth() + 1,
                1,
            );

            render();
        });

        grid.addEventListener("keydown", (event) => {
            const moves = {
                ArrowLeft: -1,
                ArrowRight: 1,
                ArrowUp: -7,
                ArrowDown: 7,
            };

            if (event.key in moves) {
                event.preventDefault();
                focusDay(moves[event.key]);
                return;
            }

            if (event.key === "Home") {
                event.preventDefault();
                focusDay(0, "start");
                return;
            }

            if (event.key === "End") {
                event.preventDefault();
                focusDay(0, "end");
                return;
            }

            if (event.key === "PageUp" || event.key === "PageDown") {
                event.preventDefault();

                visibleMonth = new Date(
                    visibleMonth.getFullYear(),
                    visibleMonth.getMonth() +
                        (event.key === "PageUp" ? -1 : 1),
                    1,
                );

                render();

                calendar
                    .querySelector('.calendar__day[tabindex="0"]')
                    ?.focus();
            }
        });
    };

    const open = (skipFocus) => {
        if (toggle.disabled || popover.matches(":popover-open")) {
            return;
        }

        render();
        popover.showPopover();

        if (!skipFocus) {
            calendar.querySelector('.calendar__day[tabindex="0"]')?.focus();
        }
    };

    toggle.addEventListener("click", () => {
        if (popover.matches(":popover-open")) {
            popover.hidePopover();
        } else {
            open(false);
        }
    });

    input.addEventListener("click", () => {
        open(true);
    });

    input.addEventListener("input", () => {
        const parsed = parseInput(input.value);
        const empty = input.value.trim() === "";
        const valid = empty || (parsed && !isDisabled(parsed));

        input.toggleAttribute("aria-invalid", !valid);
        alert.textContent = valid ? "" : "Fecha no válida";

        if (parsed && !isDisabled(parsed)) {
            selected = parsed;
            carrier.value = toISO(parsed);
            visibleMonth = new Date(
                parsed.getFullYear(),
                parsed.getMonth(),
                1,
            );
        } else if (empty) {
            carrier.value = "";
        }
    });

    input.addEventListener("blur", () => {
        const parsed = parseInput(input.value);

        if (input.value.trim() && parsed && !isDisabled(parsed)) {
            setValue(parsed);
        } else if (!input.value.trim()) {
            setValue(null);
        } else {
            input.value = formatInput(selected);
        }
    });

    input.addEventListener("keydown", (event) => {
        if (event.key === "ArrowDown") {
            event.preventDefault();
            open(true);
            return;
        }

        if (event.key === "Escape" && popover.matches(":popover-open")) {
            popover.hidePopover();
            return;
        }

        if (event.key === "Enter") {
            event.preventDefault();
            input.blur();
        }
    });

    clear?.addEventListener("click", () => {
        setValue(null);
        input.focus();
    });

    popover.addEventListener("keydown", (event) => {
        if (event.key !== "Tab") {
            return;
        }

        const focusable = Array.from(
            popover.querySelectorAll("button:not(:disabled)"),
        );

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    popover.addEventListener("toggle", (event) => {
        const openState = event.newState === "open";

        input.setAttribute("aria-expanded", String(openState));
        input.toggleAttribute("aria-controls", openState);

        if (openState) {
            input.setAttribute("aria-controls", popover.id);
        }

        toggle.setAttribute(
            "aria-label",
            openState ? "Cerrar calendario" : "Abrir calendario",
        );

        if (!openState && !root.contains(document.activeElement)) {
            input.focus({ preventScroll: true });
        }
    });
}
