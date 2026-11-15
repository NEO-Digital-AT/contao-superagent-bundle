/*
 * Back end of the SuperAgent: buttons that copy a value (e.g. the redirect
 * address in the setup steps of a connection template). One listener on the
 * document, so it also works after Contao replaced the form (Turbo).
 */
(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    var button = event.target instanceof Element ? event.target.closest('[data-nsa-copy]') : null;

    if (!button || !navigator.clipboard) {
      return;
    }

    var status = button.parentNode ? button.parentNode.querySelector('.nsa-copy__status') : null;

    navigator.clipboard.writeText(button.getAttribute('data-nsa-copy') || '').then(function () {
      if (status) {
        status.textContent = button.getAttribute('data-nsa-copied') || '';
        setTimeout(function () { status.textContent = ''; }, 2000);
      }
    });
  });
})();
