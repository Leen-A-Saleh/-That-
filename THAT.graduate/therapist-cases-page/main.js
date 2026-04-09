
document.querySelectorAll('.sidebar-nav li').forEach(li => {
  li.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-nav li').forEach(i => i.classList.remove('active'));
    li.classList.add('active');
  });
});
const filterToggle = document.getElementById('filterToggle');
const filterMenu = document.getElementById('filterMenu');
const filterLabel = document.getElementById('filterLabel');

if (filterToggle && filterMenu) {
  filterToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    filterMenu.classList.toggle('open');
  });

  document.addEventListener('click', () => {
    filterMenu.classList.remove('open');
  });

  filterMenu.querySelectorAll('.filter-item').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      filterMenu.querySelectorAll('.filter-item').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filterLabel.textContent = btn.textContent.trim();
      filterMenu.classList.remove('open');
      applyFilters();
    });
  });
}


const searchInput = document.getElementById('caseSearch');

function applyFilters() {
  const query = (searchInput?.value || '').trim();
  const activeFilterBtn = filterMenu?.querySelector('.filter-item.active');
  const filterType = activeFilterBtn ? activeFilterBtn.dataset.filter : 'all';

  document.querySelectorAll('.case-card').forEach(card => {
    const name = card.dataset.name || '';
    const urgency = card.dataset.urgency || '';
    const status = card.dataset.status || '';

   
    let okFilter = true;
    if (filterType === 'active') {
      okFilter = status === 'active';
    } else if (filterType === 'low') {
      okFilter = urgency === 'low';
    } else if (filterType === 'medium') {
      okFilter = urgency === 'medium';
    } else if (filterType === 'high') {
      okFilter = urgency === 'high';
    }

    
    let okSearch = true;
    if (query) {
      okSearch = name.includes(query);
    }

    card.style.display = okFilter && okSearch ? '' : 'none';
  });
}

if (searchInput) {
  searchInput.addEventListener('input', applyFilters);
}


applyFilters();