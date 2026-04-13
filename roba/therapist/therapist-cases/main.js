document.querySelectorAll('.sidebar-nav li').forEach(li => {
  li.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-nav li').forEach(i => i.classList.remove('active'));
    li.classList.add('active');
  });
});

const filterToggle = document.getElementById('filterToggle');
const filterMenu = document.getElementById('filterMenu');

if (filterToggle && filterMenu) {
  filterToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    filterMenu.classList.toggle('open');
  });

  document.addEventListener('click', () => {
    filterMenu.classList.remove('open');
  });

  filterMenu.addEventListener('click', (e) => {
    e.stopPropagation();
  });
}

// Submit search form on Enter or after typing stops
const searchInput = document.getElementById('caseSearch');
let searchTimer;

if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      document.getElementById('searchForm').submit();
    }, 600);
  });
}
