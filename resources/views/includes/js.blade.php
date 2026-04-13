  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <!-- JS Libraries -->
  <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
  <!-- Page Specific JS File -->
  <script src="{{ asset('assets/js/page/index.js') }}"></script>
  <!-- Template JS File -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  <!-- Custom JS File -->
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  <!-- Small initializer: feather icons + theme exposure -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      try {
        if (window.feather && typeof window.feather.replace === 'function') {
          window.feather.replace({ 'stroke-width': 1.5 });
        }
      } catch (e) {
        // ignore
      }
      // expose primary color to scripts if not already set
      window.__SITE_THEME = window.__SITE_THEME || {};
      if (!window.__SITE_THEME.primary) {
        var rootStyle = getComputedStyle(document.documentElement);
        window.__SITE_THEME.primary = rootStyle.getPropertyValue('--color-primary') || '#004080';
      }
    });
  </script>

      <!-- Small fallback to open/close dropdown menus if Bootstrap JS is missing or uses a different data attribute API -->
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          // find nav dropdown toggles (user menu)
          document.querySelectorAll('.nav-link.dropdown-toggle').forEach(function (toggle) {
            // if bootstrap already handled it (aria-expanded present), skip
            if (toggle.getAttribute('data-bs-toggle') || toggle.getAttribute('data-toggle')) {
              return;
            }

            toggle.addEventListener('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              var menu = toggle.closest('.dropdown') ? toggle.closest('.dropdown').querySelector('.dropdown-menu') : null;
              if (!menu) return;
              var isShown = menu.classList.contains('show');
              // hide any other open dropdowns
              document.querySelectorAll('.dropdown-menu.show').forEach(function (m) {
                if (m !== menu) {
                  m.classList.remove('show');
                  var parentToggle = m.parentElement && m.parentElement.querySelector('.dropdown-toggle');
                  if (parentToggle) parentToggle.setAttribute('aria-expanded', 'false');
                }
              });

              if (isShown) {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
              } else {
                menu.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
              }
            });
          });

          // close dropdowns on outside click
          document.addEventListener('click', function (e) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function (m) {
              // if click is inside this dropdown, ignore
              if (m.contains(e.target)) return;
              m.classList.remove('show');
              var parentToggle = m.parentElement && m.parentElement.querySelector('.dropdown-toggle');
              if (parentToggle) parentToggle.setAttribute('aria-expanded', 'false');
            });
          });
        });
      </script>
