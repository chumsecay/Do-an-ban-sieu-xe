document.addEventListener('DOMContentLoaded', () => {
  const searchBox = document.getElementById('navSearchBox');
  const searchBtn = document.getElementById('navSearchBtn');
  const searchInput = document.getElementById('navSearchInput');
  const searchResults = document.getElementById('navSearchResults');

  if (!searchBox || !searchBtn) return;

  function openSearch() {
    searchBox.classList.add('expanded');
    setTimeout(() => { searchInput.focus(); }, 300);
  }

  function closeSearch() {
    searchBox.classList.remove('expanded');
    searchInput.value = '';
    searchResults.classList.remove('show');
    setTimeout(() => { searchResults.classList.add('d-none'); }, 300);
  }

  searchBtn.addEventListener('click', (e) => {
    e.preventDefault();
    if (searchBox.classList.contains('expanded')) {
       // if already expanded, perform search or focus
       searchInput.focus();
    } else {
       openSearch();
    }
  });

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (!searchBox.contains(e.target) && !searchResults.contains(e.target)) {
      if (searchBox.classList.contains('expanded')) {
        closeSearch();
      }
    }
  });

  // Escape to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchBox.classList.contains('expanded')) {
      closeSearch();
    }
  });

  // Mock search logic for dropdown
  searchInput.addEventListener('input', (e) => {
    const val = e.target.value.trim();
    if (val.length > 0) {
      searchResults.classList.remove('d-none');
      // Timeout to allow display:block to apply before animating opacity
      setTimeout(() => {
        searchResults.classList.add('show');
      }, 10);
    } else {
      searchResults.classList.remove('show');
      setTimeout(() => { searchResults.classList.add('d-none'); }, 300);
    }
  });
});
