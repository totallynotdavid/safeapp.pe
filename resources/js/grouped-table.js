for (const toggle of document.querySelectorAll("[data-table-group-toggle]")) {
    const rows = document.getElementById(
        toggle.getAttribute("aria-controls") ?? "",
    );

    if (
        !(toggle instanceof HTMLTableRowElement) ||
        !(rows instanceof HTMLTableSectionElement)
    ) {
        continue;
    }

    const setExpanded = (expanded) => {
        toggle.setAttribute("aria-expanded", String(expanded));
        rows.hidden = !expanded;
    };

    const toggleExpanded = () => {
        setExpanded(toggle.getAttribute("aria-expanded") !== "true");
    };

    toggle.addEventListener("click", toggleExpanded);

    toggle.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            toggleExpanded();
        }
    });
}
