document.addEventListener("DOMContentLoaded", () => {
  const workspace = document.querySelector("[data-request-workspace]");
  if (!workspace) return;

  const choices = Array.from(
    workspace.querySelectorAll("[data-request-choice]"),
  );
  const panels = Array.from(workspace.querySelectorAll("[data-request-panel]"));

  const activate = (type, focus = true) => {
    if (type === "feb" && new URL(window.location.href).searchParams.get("type") !== "feb") {
      const target = new URL(window.location.href);
      target.searchParams.set("type", "feb");
      target.searchParams.delete("chantier");
      target.searchParams.delete("scope");
      window.location.assign(target.href);
      return;
    }
    choices.forEach((choice) =>
      choice.classList.toggle(
        "is-active",
        choice.dataset.requestChoice === type,
      ),
    );
    panels.forEach((panel) => {
      const active = panel.dataset.requestPanel === type;
      panel.hidden = !active;
      panel.classList.toggle("is-active", active);
      if (active && focus)
        panel
          .querySelector("input:not([type=hidden]), select, textarea")
          ?.focus();
    });
    if (history.replaceState) {
      const url = new URL(window.location.href);
      url.searchParams.set("type", type);
      history.replaceState({}, "", url);
    }
  };

  choices.forEach((choice) =>
    choice.addEventListener("click", () =>
      activate(choice.dataset.requestChoice),
    ),
  );
  const initial =
    choices.find((choice) => choice.classList.contains("is-active"))?.dataset
      .requestChoice || choices[0]?.dataset.requestChoice;
  if (initial) activate(initial, false);
});
