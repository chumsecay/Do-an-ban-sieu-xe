document.addEventListener('DOMContentLoaded', () => {
  const searchBox = document.getElementById('navSearchBox');
  const searchBtn = document.getElementById('navSearchBtn');
  const searchInput = document.getElementById('navSearchInput');
  const searchResults = document.getElementById('navSearchResults');

  const accountWrapper = document.getElementById('navAccountWrapper');
  const accountToggle = document.getElementById('navAccountToggle');
  const accountMenu = document.getElementById('navAccountMenu');

  let closeSearch = () => {};
  let closeAccount = () => {};

  if (searchBox && searchBtn && searchInput && searchResults) {
    function openSearch() {
      searchBox.classList.add('expanded');
      setTimeout(() => {
        searchInput.focus();
      }, 300);
    }

    closeSearch = function closeSearchImpl() {
      searchBox.classList.remove('expanded');
      searchInput.value = '';
      searchResults.classList.remove('show');
      setTimeout(() => {
        searchResults.classList.add('d-none');
      }, 300);
    };

    searchBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeAccount();

      if (searchBox.classList.contains('expanded')) {
        searchInput.focus();
      } else {
        openSearch();
      }
    });

    searchInput.addEventListener('input', (e) => {
      const val = e.target.value.trim();
      if (val.length > 0) {
        searchResults.classList.remove('d-none');
        setTimeout(() => {
          searchResults.classList.add('show');
        }, 10);
      } else {
        searchResults.classList.remove('show');
        setTimeout(() => {
          searchResults.classList.add('d-none');
        }, 300);
      }
    });
  }

  if (accountWrapper && accountToggle && accountMenu) {
    const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;

    function openAccount() {
      accountWrapper.classList.add('open');
      accountToggle.setAttribute('aria-expanded', 'true');
    }

    closeAccount = function closeAccountImpl() {
      accountWrapper.classList.remove('open');
      accountToggle.setAttribute('aria-expanded', 'false');
    };

    accountToggle.addEventListener('click', (e) => {
      if (isDesktop()) {
        return;
      }

      e.preventDefault();
      e.stopPropagation();
      closeSearch();

      if (accountWrapper.classList.contains('open')) {
        closeAccount();
      } else {
        openAccount();
      }
    });

    accountMenu.querySelectorAll('a').forEach((item) => {
      item.addEventListener('click', () => {
        closeAccount();
      });
    });
  }

  document.addEventListener('click', (e) => {
    if (searchBox && searchResults && searchBox.classList.contains('expanded')) {
      if (!searchBox.contains(e.target) && !searchResults.contains(e.target)) {
        closeSearch();
      }
    }

    if (accountWrapper && accountWrapper.classList.contains('open')) {
      if (!accountWrapper.contains(e.target)) {
        closeAccount();
      }
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeSearch();
      closeAccount();
    }
  });
});
