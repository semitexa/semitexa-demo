(function () {
  function mount(root) {
    var button = root.querySelector('[data-product-spotlight-action]');
    var status = root.querySelector('[data-product-spotlight-status]');

    if (status) {
      status.textContent = 'Enhanced by the component script asset.';
    }

    if (button) {
      button.addEventListener('click', function () {
        if (status) {
          status.textContent = 'The component handled this interaction without page-level glue.';
        }
      });
    }
  }

  window.SemitexaComponent.register('product-spotlight-card', mount);
}());
