for (const field of document.querySelectorAll("[data-selector]")) {
    const container = field.querySelector(".selector");
    const trigger = field.querySelector(".selector__trigger");
    const label = field.querySelector(".selector__label");
    const carrier = field.querySelector('input[type="hidden"]');
    const listboxId = trigger?.getAttribute("aria-controls");
    const listbox = listboxId ? document.getElementById(listboxId) : null;
    const options = Array.from(
        listbox?.querySelectorAll('[role="option"]') ?? [],
    );
    let highlightedIndex = options.findIndex(
        (option) => option.getAttribute("aria-selected") === "true",
    );
    let typeahead = "";
    let typeaheadTimeout;

    if (
        !(container instanceof HTMLElement) ||
        !(trigger instanceof HTMLButtonElement) ||
        !(label instanceof HTMLElement) ||
        !(carrier instanceof HTMLInputElement) ||
        !(listbox instanceof HTMLElement)
    ) {
        continue;
    }

    const enabledIndexes = () =>
        options
            .map((option, index) =>
                option.getAttribute("aria-disabled") === "true" ? -1 : index,
            )
            .filter((index) => index >= 0);

    const highlight = (index) => {
        for (const option of options) {
            option.removeAttribute("data-highlighted");
        }

        if (
            index < 0 ||
            options[index]?.getAttribute("aria-disabled") === "true"
        ) {
            highlightedIndex = -1;
            trigger.removeAttribute("aria-activedescendant");
            return;
        }

        highlightedIndex = index;
        const option = options[index];

        option.setAttribute("data-highlighted", "true");
        trigger.setAttribute("aria-activedescendant", option.id);
        option.scrollIntoView({ block: "nearest" });
    };

    const open = () => {
        if (trigger.disabled || listbox.matches(":popover-open")) {
            return;
        }

        listbox.showPopover();

        const selectedIndex = options.findIndex(
            (option) => option.getAttribute("aria-selected") === "true",
        );
        const firstEnabled = enabledIndexes()[0] ?? -1;

        highlight(selectedIndex >= 0 ? selectedIndex : firstEnabled);
    };

    const close = () => {
        highlight(-1);

        if (listbox.matches(":popover-open")) {
            listbox.hidePopover();
        }
    };

    const select = (option) => {
        if (
            !(option instanceof HTMLElement) ||
            option.getAttribute("aria-disabled") === "true"
        ) {
            return;
        }

        for (const candidate of options) {
            candidate.setAttribute(
                "aria-selected",
                String(candidate === option),
            );
        }

        carrier.value = option.dataset.value ?? "";
        label.textContent =
            option
                .querySelector(".selector__option-content")
                ?.textContent?.trim() ?? "";
        container.setAttribute("data-placeholder", "false");
        carrier.dispatchEvent(new Event("change", { bubbles: true }));

        close();
        trigger.focus({ preventScroll: true });
    };

    container.addEventListener("click", () => {
        if (listbox.matches(":popover-open")) {
            close();
        } else {
            open();
        }
    });

    listbox.addEventListener("click", (event) => {
        const option =
            event.target instanceof Element
                ? event.target.closest('[role="option"]')
                : null;

        if (option && listbox.contains(option)) {
            select(option);
        }
    });

    for (const [index, option] of options.entries()) {
        option.addEventListener("mouseenter", () => highlight(index));
    }

    trigger.addEventListener("keydown", (event) => {
        const indexes = enabledIndexes();
        const currentPosition = indexes.indexOf(highlightedIndex);
        const isOpen = listbox.matches(":popover-open");

        if (event.key === "ArrowDown") {
            event.preventDefault();

            if (!isOpen) {
                open();
                highlight(indexes[0] ?? -1);
            } else {
                const nextPosition = Math.min(
                    currentPosition + 1,
                    indexes.length - 1,
                );
                highlight(indexes[nextPosition] ?? highlightedIndex);
            }
        } else if (event.key === "ArrowUp") {
            event.preventDefault();

            if (!isOpen) {
                open();
                highlight(indexes.at(-1) ?? -1);
            } else {
                const previousPosition = Math.max(currentPosition - 1, 0);
                highlight(indexes[previousPosition] ?? highlightedIndex);
            }
        } else if (event.key === "Home" || event.key === "End") {
            event.preventDefault();

            if (isOpen) {
                highlight(
                    event.key === "Home"
                        ? (indexes[0] ?? -1)
                        : (indexes.at(-1) ?? -1),
                );
            }
        } else if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();

            if (isOpen && highlightedIndex >= 0) {
                select(options[highlightedIndex]);
            } else {
                open();
            }
        } else if (event.key === "Escape") {
            if (isOpen) {
                event.preventDefault();
                close();
            }
        } else if (event.key === "Tab") {
            if (isOpen) {
                close();
            }
        } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey) {
            typeahead += event.key.toLocaleLowerCase();
            window.clearTimeout(typeaheadTimeout);

            typeaheadTimeout = window.setTimeout(() => {
                typeahead = "";
            }, 500);

            const matchIndex = options.findIndex(
                (option) =>
                    option.getAttribute("aria-disabled") !== "true" &&
                    option
                        .querySelector(".selector__option-content")
                        ?.textContent?.trim()
                        .toLocaleLowerCase()
                        .startsWith(typeahead),
            );

            if (matchIndex >= 0) {
                if (!isOpen) {
                    open();
                }

                highlight(matchIndex);
            }
        }
    });

    listbox.addEventListener("toggle", (event) => {
        const isOpen = event.newState === "open";

        trigger.setAttribute("aria-expanded", String(isOpen));
        container.toggleAttribute("data-open", isOpen);

        if (!isOpen) {
            trigger.removeAttribute("aria-activedescendant");
        }
    });
}

for (const form of document.querySelectorAll("[data-store-assignment]")) {
    const assignmentCheckboxes = Array.from(
        form.querySelectorAll("[data-store-checkbox]"),
    );
    const activeRadios = Array.from(
        form.querySelectorAll('input[type="radio"][name="activa"]'),
    );

    const syncActiveStores = () => {
        const assigned = new Set(
            assignmentCheckboxes
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.value),
        );

        for (const radio of activeRadios) {
            radio.disabled = !assigned.has(radio.value);

            if (radio.disabled) {
                radio.checked = false;
            }
        }
    };

    for (const checkbox of assignmentCheckboxes) {
        checkbox.addEventListener("change", syncActiveStores);
    }
}

const mobileSideNavQuery = window.matchMedia("(max-width: 768px)");

for (const region of document.querySelectorAll("[data-side-nav-resizable]")) {
    const nav = region.querySelector("[data-side-nav]");
    const handle = region.querySelector("[data-side-nav-resize-handle]");
    const collapseButton = region.querySelector("[data-side-nav-collapse]");

    if (
        !(region instanceof HTMLElement) ||
        !(nav instanceof HTMLElement) ||
        !(handle instanceof HTMLElement) ||
        !(collapseButton instanceof HTMLButtonElement)
    ) {
        continue;
    }

    const defaultWidth = Number(region.dataset.defaultWidth ?? 300);
    const minWidth = Number(region.dataset.minWidth ?? 220);
    const maxWidth = Number(region.dataset.maxWidth ?? 420);
    const collapseThreshold = 160;
    let width = Math.min(maxWidth, Math.max(minWidth, defaultWidth));
    let preCollapseWidth = width;
    let isCollapsed = false;
    let dragStartWidth = width;
    let dragStartX = 0;

    const sectionTriggers = Array.from(
        nav.querySelectorAll("[data-side-nav-toggle]"),
    );

    const closeCollapsedPopovers = () => {
        for (const trigger of sectionTriggers) {
            const group = document.getElementById(
                trigger.getAttribute("aria-controls") ?? "",
            );

            if (
                group instanceof HTMLElement &&
                group.matches(":popover-open")
            ) {
                group.hidePopover();
            }
        }
    };

    const syncPopoverTargets = () => {
        const effectiveCollapsed = isCollapsed && !mobileSideNavQuery.matches;

        for (const [index, trigger] of sectionTriggers.entries()) {
            const group = document.getElementById(
                trigger.getAttribute("aria-controls") ?? "",
            );

            if (
                !(trigger instanceof HTMLElement) ||
                !(group instanceof HTMLElement)
            ) {
                continue;
            }

            if (effectiveCollapsed) {
                if (!group.hasAttribute("popover")) {
                    group.dataset.inlineExpanded =
                        trigger.getAttribute("aria-expanded") ?? "false";
                    group.setAttribute("popover", "auto");
                }

                const anchorName = `--side-nav-item-${index}`;

                trigger.style.anchorName = anchorName;
                group.style.positionAnchor = anchorName;
                group.setAttribute("aria-hidden", "false");
                group.removeAttribute("inert");
                trigger.setAttribute(
                    "aria-expanded",
                    String(group.matches(":popover-open")),
                );
                continue;
            }

            if (group.matches(":popover-open")) {
                group.hidePopover();
            }

            if (group.hasAttribute("popover")) {
                group.removeAttribute("popover");
                group.style.removeProperty("position-anchor");
                trigger.style.removeProperty("anchor-name");

                const expanded = group.dataset.inlineExpanded === "true";

                trigger.setAttribute("aria-expanded", String(expanded));
                group.setAttribute("aria-hidden", String(!expanded));
                group.toggleAttribute("inert", !expanded);
                delete group.dataset.inlineExpanded;
            }
        }
    };

    const render = () => {
        const effectiveCollapsed = isCollapsed && !mobileSideNavQuery.matches;

        if (effectiveCollapsed) {
            nav.setAttribute("data-collapsed", "true");
            region.setAttribute("data-collapsed", "true");
        } else {
            nav.removeAttribute("data-collapsed");
            region.removeAttribute("data-collapsed");
        }

        nav.style.width = effectiveCollapsed ? "" : `${width}px`;
        handle.setAttribute(
            "aria-valuenow",
            String(effectiveCollapsed ? 0 : width),
        );
        collapseButton.setAttribute(
            "aria-expanded",
            String(!effectiveCollapsed),
        );
        collapseButton.setAttribute(
            "aria-label",
            effectiveCollapsed
                ? "Expandir barra lateral"
                : "Contraer barra lateral",
        );

        if (!effectiveCollapsed) {
            closeCollapsedPopovers();
        }

        syncPopoverTargets();
    };

    const setCollapsed = (collapsed) => {
        if (collapsed === isCollapsed) {
            return;
        }

        closeCollapsedPopovers();

        if (collapsed) {
            preCollapseWidth = width;
        } else {
            width = Math.min(
                maxWidth,
                Math.max(minWidth, preCollapseWidth || defaultWidth),
            );
        }

        isCollapsed = collapsed;
        render();
    };

    const resizeFrom = (nextWidth) => {
        if (nextWidth < collapseThreshold) {
            setCollapsed(true);
            return;
        }

        if (isCollapsed) {
            isCollapsed = false;
        }

        width = Math.min(maxWidth, Math.max(minWidth, nextWidth));
        render();
    };

    const stopDragging = () => {
        handle.removeAttribute("data-resizing");
        document.body.style.cursor = "";
        document.body.style.userSelect = "";
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", stopDragging);
        window.removeEventListener("pointercancel", stopDragging);
    };

    const onPointerMove = (event) =>
        resizeFrom(dragStartWidth + (event.clientX - dragStartX));

    handle.addEventListener("pointerdown", (event) => {
        event.preventDefault();
        event.stopPropagation();

        dragStartWidth = isCollapsed ? 0 : width;
        dragStartX = event.clientX;

        handle.setAttribute("data-resizing", "true");
        document.body.style.cursor = "col-resize";
        document.body.style.userSelect = "none";
        window.addEventListener("pointermove", onPointerMove);
        window.addEventListener("pointerup", stopDragging);
        window.addEventListener("pointercancel", stopDragging);
    });

    handle.addEventListener("keydown", (event) => {
        const step = event.shiftKey ? 50 : 10;

        if (event.key === "ArrowRight" || event.key === "ArrowDown") {
            event.preventDefault();
            resizeFrom((isCollapsed ? 0 : width) + step);
        } else if (event.key === "ArrowLeft" || event.key === "ArrowUp") {
            event.preventDefault();
            resizeFrom((isCollapsed ? 0 : width) - step);
        } else if (event.key === "Home") {
            event.preventDefault();
            resizeFrom(minWidth);
        } else if (event.key === "End") {
            event.preventDefault();
            resizeFrom(maxWidth);
        } else if (event.key === "Enter") {
            event.preventDefault();
            setCollapsed(!isCollapsed);
        }
    });

    handle.addEventListener("dblclick", () => setCollapsed(!isCollapsed));

    collapseButton.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopImmediatePropagation();
        setCollapsed(!isCollapsed);
    });

    for (const trigger of sectionTriggers) {
        const group = document.getElementById(
            trigger.getAttribute("aria-controls") ?? "",
        );

        if (!(group instanceof HTMLElement)) {
            continue;
        }

        group.addEventListener("toggle", () => {
            if (group.hasAttribute("popover")) {
                trigger.setAttribute(
                    "aria-expanded",
                    String(group.matches(":popover-open")),
                );
            }
        });

        group.addEventListener("click", (event) => {
            if (
                group.hasAttribute("popover") &&
                event.target instanceof Element &&
                event.target.closest("a")
            ) {
                group.hidePopover();
            }
        });
    }

    mobileSideNavQuery.addEventListener("change", render);
    render();
}

for (const nav of document.querySelectorAll("[data-side-nav]")) {
    nav.addEventListener("click", (event) => {
        const toggle =
            event.target instanceof Element
                ? event.target.closest("[data-side-nav-toggle]")
                : null;

        if (!(toggle instanceof HTMLButtonElement) || !nav.contains(toggle)) {
            return;
        }

        const controls = toggle.getAttribute("aria-controls");
        const group = controls ? document.getElementById(controls) : null;

        if (
            nav.hasAttribute("data-collapsed") &&
            group instanceof HTMLElement &&
            group.hasAttribute("popover")
        ) {
            if (group.matches(":popover-open")) {
                group.hidePopover();
            } else {
                group.showPopover();
            }

            return;
        }

        if (!group) {
            return;
        }

        const expanded = toggle.getAttribute("aria-expanded") === "true";

        toggle.setAttribute("aria-expanded", String(!expanded));
        group.setAttribute("aria-hidden", String(expanded));
        group.toggleAttribute("inert", expanded);
    });
}

for (const control of document.querySelectorAll("[data-auto-submit]")) {
    control.addEventListener("change", () => control.form?.requestSubmit());
}

for (const dismiss of document.querySelectorAll("[data-toast-dismiss]")) {
    dismiss.addEventListener("click", () => {
        const wrapper = dismiss.closest("[data-toast-wrapper]");

        if (!wrapper) {
            return;
        }

        wrapper.setAttribute("data-exiting", "true");

        window.setTimeout(
            () => wrapper.remove(),
            window.matchMedia("(prefers-reduced-motion: reduce)").matches
                ? 10
                : 125,
        );
    });
}

// Alert dialogs do not light-dismiss; Escape acts as cancel.
for (const alertDialog of document.querySelectorAll("[data-alert-dialog]")) {
    if (!(alertDialog instanceof HTMLDialogElement)) {
        continue;
    }

    const cancel = alertDialog.querySelector("[data-alert-dialog-cancel]");
    let invoker = null;

    const close = () => {
        if (alertDialog.open) {
            alertDialog.close();
        }
    };

    for (const trigger of document.querySelectorAll(
        `[data-alert-dialog-open="${alertDialog.id}"]`,
    )) {
        trigger.addEventListener("click", () => {
            if (alertDialog.open) {
                return;
            }

            invoker = trigger;

            const rect = trigger.getBoundingClientRect();
            const dx = rect.left + rect.width / 2 - window.innerWidth / 2;
            const dy = rect.top + rect.height / 2 - window.innerHeight / 2;
            const distance = Math.sqrt(dx * dx + dy * dy) || 1;

            alertDialog.style.setProperty(
                "--dialog-dir-x",
                `${Math.round((dx / distance) * 16)}px`,
            );
            alertDialog.style.setProperty(
                "--dialog-dir-y",
                `${Math.round((dy / distance) * 16)}px`,
            );
            alertDialog.showModal();

            if (cancel instanceof HTMLElement) {
                cancel.focus({ preventScroll: true });
            }
        });
    }

    cancel?.addEventListener("click", close);

    alertDialog.addEventListener("cancel", (event) => {
        event.preventDefault();
        close();
    });

    alertDialog.addEventListener("close", () => {
        if (invoker instanceof HTMLElement) {
            invoker.focus({ preventScroll: true });
        }

        invoker = null;
    });
}

// Native <dialog> handles focus containment.
for (const dialog of document.querySelectorAll("[data-dialog]")) {
    if (!(dialog instanceof HTMLDialogElement)) {
        continue;
    }

    let invoker = null;

    const open = (trigger) => {
        if (dialog.open) {
            return;
        }

        invoker = trigger instanceof HTMLElement ? trigger : null;

        if (invoker) {
            const rect = invoker.getBoundingClientRect();
            const dx = rect.left + rect.width / 2 - window.innerWidth / 2;
            const dy = rect.top + rect.height / 2 - window.innerHeight / 2;
            const distance = Math.sqrt(dx * dx + dy * dy) || 1;

            dialog.style.setProperty(
                "--dialog-dir-x",
                `${Math.round((dx / distance) * 16)}px`,
            );
            dialog.style.setProperty(
                "--dialog-dir-y",
                `${Math.round((dy / distance) * 16)}px`,
            );
        }

        dialog.showModal();

        const title = dialog.querySelector("[data-dialog-title]");

        if (title instanceof HTMLElement) {
            title.focus({ preventScroll: true });
        }
    };

    for (const trigger of document.querySelectorAll(
        `[data-dialog-open="${dialog.id}"]`,
    )) {
        trigger.addEventListener("click", () => open(trigger));
    }

    for (const close of dialog.querySelectorAll("[data-dialog-close]")) {
        close.addEventListener("click", () => dialog.close());
    }

    dialog.addEventListener("close", () => {
        if (invoker instanceof HTMLElement) {
            invoker.focus({ preventScroll: true });
        }

        invoker = null;
    });

    if (dialog.hasAttribute("data-dialog-open-on-load")) {
        open(null);
    }
}

const dialog = document.querySelector("[data-mobile-nav]");
const openButton = document.querySelector("[data-mobile-nav-open]");
const closeButton = dialog?.querySelector("[data-mobile-nav-close]");
const drawer = dialog?.querySelector("[data-mobile-nav-drawer]");
const mobileNavContent = dialog?.querySelector("[data-mobile-nav-content]");
const sideNavHome = document.querySelector("[data-side-nav-home]");
const sideNavRegion = sideNavHome?.querySelector("[data-side-nav-resizable]");
const desktopQuery = window.matchMedia("(min-width: 768.02px)");
let invoker = null;
let closeTimer = null;
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

const syncSideNavLocation = () => {
    if (
        !(sideNavRegion instanceof HTMLElement) ||
        !(sideNavHome instanceof HTMLElement) ||
        !(mobileNavContent instanceof HTMLElement)
    ) {
        return;
    }

    const target = mobileSideNavQuery.matches ? mobileNavContent : sideNavHome;

    if (sideNavRegion.parentElement !== target) {
        target.append(sideNavRegion);
    }
};

mobileSideNavQuery.addEventListener("change", syncSideNavLocation);
syncSideNavLocation();

const finishClose = () => {
    if (!(dialog instanceof HTMLDialogElement) || !dialog.open) {
        return;
    }

    dialog.close();
};

const closeMobileNav = () => {
    if (
        !(dialog instanceof HTMLDialogElement) ||
        !dialog.open ||
        closeTimer !== null
    ) {
        return;
    }

    dialog.removeAttribute("data-open");

    closeTimer = window.setTimeout(
        () => {
            closeTimer = null;
            finishClose();
        },
        reducedMotion.matches ? 10 : 300,
    );
};

if (
    dialog instanceof HTMLDialogElement &&
    openButton instanceof HTMLButtonElement &&
    closeButton instanceof HTMLButtonElement
) {
    openButton.addEventListener("click", () => {
        if (dialog.open) {
            return;
        }

        invoker = openButton;
        dialog.showModal();
        openButton.setAttribute("aria-expanded", "true");

        window.requestAnimationFrame(() => {
            dialog.setAttribute("data-open", "true");
            drawer?.focus({ preventScroll: true });
        });
    });

    closeButton.addEventListener("click", closeMobileNav);

    mobileNavContent?.addEventListener("click", (event) => {
        if (event.target instanceof Element && event.target.closest("a")) {
            closeMobileNav();
        }
    });

    dialog.addEventListener("click", (event) => {
        if (event.target === dialog) {
            closeMobileNav();
        }
    });

    dialog.addEventListener("cancel", (event) => {
        event.preventDefault();
        closeMobileNav();
    });

    dialog.addEventListener("close", () => {
        dialog.removeAttribute("data-open");
        openButton.setAttribute("aria-expanded", "false");
        invoker?.focus({ preventScroll: true });
        invoker = null;
    });

    desktopQuery.addEventListener("change", (event) => {
        if (event.matches) {
            if (closeTimer !== null) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }

            finishClose();
        }
    });
}
