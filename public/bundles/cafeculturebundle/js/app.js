/**
 * CafeCultureBundle — app.js
 * Scripts frontend : rating, navbar mobile, flash dismiss, search autocomplete
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Navbar toggle mobile ─────────────────────────────────────────────────
  const toggle = document.querySelector('.navbar__toggle');
  const menu   = document.querySelector('.navbar__menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const open = menu.style.display === 'flex';
      menu.style.display = open ? 'none' : 'flex';
      menu.style.flexDirection = 'column';
      menu.style.position = 'absolute';
      menu.style.top = '64px';
      menu.style.left = '0';
      menu.style.right = '0';
      menu.style.background = 'var(--cafe-espresso)';
      menu.style.padding = '1rem';
      toggle.setAttribute('aria-expanded', String(!open));
    });
  }

  // ── Dismiss flash messages ───────────────────────────────────────────────
  document.querySelectorAll('.flash__close').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.flash')?.remove();
    });
  });

  // ── Star rating widget ───────────────────────────────────────────────────
  const ratingWidget = document.querySelector('.article__rating[data-article-id]');
  if (ratingWidget) {
    const articleId = ratingWidget.dataset.articleId;
    const stars     = ratingWidget.querySelectorAll('.rating__star');
    const feedback  = ratingWidget.querySelector('.rating__feedback');
    let   rated     = false;

    stars.forEach((star, i) => {
      // Survol
      star.addEventListener('mouseenter', () => {
        if (rated) return;
        stars.forEach((s, j) => {
          s.querySelector('i').className = j <= i
            ? 'fas fa-star' : 'far fa-star';
          s.style.color = j <= i ? 'var(--cafe-amber)' : 'var(--cafe-latte)';
        });
      });

      // Remise à zéro au départ de la zone
      star.addEventListener('mouseleave', () => {
        if (rated) return;
        stars.forEach(s => {
          s.querySelector('i').className = 'far fa-star';
          s.style.color = 'var(--cafe-latte)';
        });
      });

      // Clic — envoi de la note
      star.addEventListener('click', async () => {
        if (rated) {
          feedback.textContent = 'Vous avez déjà noté cet article.';
          return;
        }
        const score = parseInt(star.dataset.score, 10);
        try {
          const response = await fetch(`/api/cafe/rate/${articleId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ score }),
          });
          const data = await response.json();
          if (response.ok && data.success) {
            rated = true;
            stars.forEach((s, j) => {
              s.style.color = j < score ? 'var(--cafe-amber)' : 'var(--cafe-latte)';
              s.disabled = true;
            });
            feedback.textContent = `Merci pour votre note ! Moyenne : ${data.average}/5 (${data.count} avis)`;
            feedback.style.color = 'var(--cafe-green, #2d6a4f)';
          } else {
            feedback.textContent = data.error ?? 'Une erreur est survenue.';
          }
        } catch {
          feedback.textContent = 'Impossible d\'envoyer votre note. Réessayez.';
        }
      });
    });
  }

  // ── Search autocomplete ──────────────────────────────────────────────────
  const searchInput = document.querySelector('.navbar__search input');
  if (searchInput) {
    let debounceTimer;
    let dropdown = null;

    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = searchInput.value.trim();

      if (q.length < 2) {
        dropdown?.remove();
        dropdown = null;
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          const res  = await fetch(`/api/cafe/search?q=${encodeURIComponent(q)}`);
          const data = await res.json();

          dropdown?.remove();

          if (!data.length) return;

          dropdown = document.createElement('ul');
          dropdown.className = 'search-dropdown';
          Object.assign(dropdown.style, {
            position: 'absolute', zIndex: '200',
            background: '#fff', borderRadius: '8px',
            boxShadow: '0 8px 32px rgba(0,0,0,.18)',
            listStyle: 'none', minWidth: '320px',
            padding: '.5rem 0', marginTop: '4px',
          });

          data.forEach(item => {
            const li = document.createElement('li');
            li.innerHTML = `<a href="/blog/article/${item.slug}" style="display:flex;flex-direction:column;padding:.6rem 1rem;color:#1a0a00;gap:.15rem;">
              <strong style="font-size:.9rem">${item.title}</strong>
              <small style="color:#9a7b65;font-size:.78rem">${item.category ?? ''}</small>
            </a>`;
            li.querySelector('a').addEventListener('mousedown', e => e.preventDefault());
            dropdown.appendChild(li);
          });

          searchInput.parentElement.style.position = 'relative';
          searchInput.parentElement.appendChild(dropdown);
        } catch { /* silently ignore */ }
      }, 280);
    });

    searchInput.addEventListener('blur', () => {
      setTimeout(() => { dropdown?.remove(); dropdown = null; }, 200);
    });
  }

  // ── Smooth scroll pour ancres ────────────────────────────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

});
