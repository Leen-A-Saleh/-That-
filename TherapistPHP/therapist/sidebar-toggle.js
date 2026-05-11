(function () {
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');

  if (!toggle || !sidebar || !overlay) {
    console.warn('Sidebar toggle: missing elements', { toggle: !!toggle, sidebar: !!sidebar, overlay: !!overlay });
    return;
  }

  toggle.addEventListener('click', function (e) {
    e.stopPropagation();
    sidebar.classList.add('open');
    overlay.classList.add('active');
  });

  overlay.addEventListener('click', function () {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
  });
})();
