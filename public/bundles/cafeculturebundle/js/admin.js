/**
 * CafeCultureBundle — admin.js
 * Scripts du back-office : prévisualisation Markdown, upload image, etc.
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Auto-génération du slug depuis le titre ──────────────────────────────
  const titleInput = document.querySelector('[name*="[title]"], #article_title');
  const slugInput  = document.querySelector('[name*="[slug]"], #article_slug');

  if (titleInput && slugInput && !slugInput.value) {
    titleInput.addEventListener('input', () => {
      slugInput.value = slugify(titleInput.value);
    });
  }

  function slugify(str) {
    return str
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  // ── Prévisualisation de l'image de couverture ────────────────────────────
  const coverInput   = document.querySelector('[name*="coverImageFile"]');
  const currentImage = document.querySelector('.current-image img');

  if (coverInput) {
    coverInput.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = ev => {
        let preview = document.getElementById('image-preview');
        if (!preview) {
          preview = document.createElement('img');
          preview.id = 'image-preview';
          Object.assign(preview.style, {
            maxWidth: '100%', borderRadius: '8px',
            marginTop: '.75rem', display: 'block',
          });
          coverInput.parentElement.insertAdjacentElement('afterend', preview);
        }
        preview.src = ev.target.result;
        preview.alt = 'Aperçu de l\'image';
      };
      reader.readAsDataURL(file);
    });
  }

  // ── Compteurs de caractères ──────────────────────────────────────────────
  const counters = [
    { selector: '#article_excerpt',         id: 'excerpt-counter',     max: 200 },
    { selector: '#article_metaTitle',       id: 'meta-title-counter',  max: 60  },
    { selector: '#article_metaDescription', id: 'meta-desc-counter',   max: 160 },
  ];

  counters.forEach(({ selector, id, max }) => {
    const field   = document.querySelector(selector);
    const counter = document.getElementById(id);
    if (!field || !counter) return;

    const update = () => {
      const len = field.value.length;
      counter.textContent = `${len} / ${max} caractères recommandés`;
      counter.className = 'char-counter' + (len > max ? ' over' : '');
    };
    field.addEventListener('input', update);
    update();
  });

  // ── Confirmation avant suppression ──────────────────────────────────────
  document.querySelectorAll('.admin-table form[action*="supprimer"]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirm('Supprimer définitivement cet élément ? Cette action est irréversible.')) {
        e.preventDefault();
      }
    });
  });

  // ── Notification auto-dismiss ────────────────────────────────────────────
  document.querySelectorAll('.flash').forEach(flash => {
    setTimeout(() => {
      flash.style.transition = 'opacity .5s';
      flash.style.opacity    = '0';
      setTimeout(() => flash.remove(), 500);
    }, 5000);
  });

  // ── Tags select2-like (multi-select simplifié) ───────────────────────────
  const tagsSelect = document.querySelector('.form-control--tags');
  if (tagsSelect) {
    tagsSelect.setAttribute('size', Math.min(tagsSelect.options.length, 8));
    const hint = document.createElement('p');
    hint.className = 'form-help';
    hint.textContent = 'Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs tags.';
    tagsSelect.parentElement.appendChild(hint);
  }

  // ── Avertissement avant de quitter si modifications non sauvegardées ─────
  let formDirty = false;
  const adminForm = document.querySelector('.admin-form');

  if (adminForm) {
    adminForm.addEventListener('input', () => { formDirty = true; });
    adminForm.addEventListener('change', () => { formDirty = true; });
    adminForm.addEventListener('submit', () => { formDirty = false; });

    window.addEventListener('beforeunload', e => {
      if (formDirty) {
        e.preventDefault();
        e.returnValue = 'Des modifications non sauvegardées seront perdues.';
      }
    });
  }

});
