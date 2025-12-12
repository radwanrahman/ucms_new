(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function openSidebar() {
    document.body.classList.add('sidebar-open');
  }

  function closeSidebar() {
    document.body.classList.remove('sidebar-open');
  }

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-sidebar-toggle]');
    if (toggle) {
      e.preventDefault();
      if (document.body.classList.contains('sidebar-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
      return;
    }

    var closeEl = e.target.closest('[data-sidebar-close]');
    if (closeEl) {
      e.preventDefault();
      closeSidebar();
      return;
    }

    // Auto-close drawer after clicking a navigation link (mobile)
    var navLink = e.target.closest('.sidebar a.sidebar-link');
    if (navLink && window.matchMedia('(max-width: 900px)').matches) {
      closeSidebar();
    }

    // Accordion toggle
    var acc = e.target.closest('[data-accordion-toggle]');
    if (acc) {
      e.preventDefault();
      var targetId = acc.getAttribute('data-accordion-toggle');
      var target = targetId ? document.getElementById(targetId) : null;
      if (!target) return;

      var expanded = acc.getAttribute('aria-expanded') === 'true';
      acc.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      target.hidden = expanded;
      return;
    }

    // Quick actions
    var action = e.target.closest('[data-quick-action]');
    if (action) {
      e.preventDefault();
      var name = action.getAttribute('data-quick-action');

      var map = {
        joinCourse: 'joinCourseModal',
        createCourse: 'createCourseModal',
        createAssignment: 'createAssignmentModal'
      };

      var modalId = map[name];
      if (modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
          // SweetAlert handles modals mostly, but if using custom ones:
          modal.style.display = 'flex';
          return;
        }
        // If using sweetalert logic mapped to these IDs (we might need to adapt if we don't have these divs)
        // But for this step, we just provide the JS.
      }

      // Fallback: scroll to announcement composer if present
      if (name === 'postAnnouncement') {
        var t = qs('textarea[name="content"]');
        if (t) {
          t.scrollIntoView({ behavior: 'smooth', block: 'center' });
          t.focus();
          return;
        }
      }

      // Final fallback: follow href if provided
      var href = action.getAttribute('href');
      if (href) window.location.href = href;
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  });
})();
