'use strict';
(function () {
  const root = document.querySelector('.supplier-workspace');
  if (!root) return;
  const radios = Array.from(root.querySelectorAll('input[name="person_type"]'));
  const panels = Array.from(root.querySelectorAll('.supplier-type-panel'));
  function update() {
    const selected = radios.find(radio => radio.checked)?.value || 'morale';
    for (const panel of panels) {
      const visible = panel.id === 'supplier-fields-' + selected;
      panel.hidden = !visible;
      for (const field of panel.querySelectorAll('input,select,textarea')) {
        field.disabled = !visible;
        field.required = visible && field.dataset.requiredFor === selected;
      }
    }
  }
  for (const radio of radios) radio.addEventListener('change', update);
  update();
})();
