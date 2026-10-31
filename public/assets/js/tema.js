// Aplica el tema claro/oscuro guardado antes de pintar la página (evita el parpadeo)
// y recuerda si el menú lateral estaba colapsado. Se carga en <head>, sin defer.
(() => {
  'use strict';
  const root = document.documentElement;
  const STORAGE_KEY = 'lte-theme';
  let stored = null;
  try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
  const authored = root.getAttribute('data-bs-theme');
  let resolved = stored || authored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  root.setAttribute('data-bs-theme', resolved);

  // Prevenir parpadeo del sidebar si fue colapsado por el usuario
  try {
    if (localStorage.getItem('lte.sidebar.state') === 'collapsed' && window.innerWidth >= 992) {
      document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('sidebar-collapse');
      });
    }
  } catch {}
})();
