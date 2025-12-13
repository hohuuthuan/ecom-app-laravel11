// @ts-nocheck
(function () {
  'use strict';

  function getTabFromURL() {
    const u = new URL(location.href);
    const t = u.searchParams.get('tab');
    if (['category', 'author', 'publisher'].includes(t)) {
      return t;
    }
    return 'category';
  }

  function setTabInURL(tab, replace) {
    const u = new URL(location.href);
    u.searchParams.set('tab', tab);
    if (replace) {
      history.replaceState(null, '', u);
      return;
    }
    history.pushState(null, '', u);
  }

  function setRightNavActive(tab) {
    document.querySelectorAll('.admin-catalog-nav-link').forEach(a => {
      a.classList.toggle('active', a.dataset.catalogNav === tab);
    });
  }

  function showPane(tab) {
    ['category', 'author', 'publisher'].forEach(k => {
      const pane = document.getElementById(`${k}-pane`);
      if (!pane) {
        return;
      }
      const on = k === tab;
      pane.classList.toggle('active', on);
      pane.classList.toggle('show', on);
      pane.setAttribute('aria-hidden', on ? 'false' : 'true');
    });
  }

  function makeHiddenInputs(container, name, values) {
    container.innerHTML = '';
    values.forEach(v => {
      const i = document.createElement('input');
      i.type = 'hidden';
      i.name = name;
      i.value = v;
      container.appendChild(i);
    });
  }

  function getCheckedValues(tableSel) {
    const cbs = document.querySelectorAll(`${tableSel} tbody input[type=checkbox]:checked`);
    return Array.from(cbs).map(x => x.value);
  }

  function updateBulkButtons() {
    const catBtn = document.getElementById('catBtnBulkDelete');
    const authorBtn = document.getElementById('authorBtnBulkDelete');
    const publisherBtn = document.getElementById('publisherBtnBulkDelete');

    if (catBtn) {
      catBtn.disabled = getCheckedValues('#categoryTable').length === 0;
    }
    if (authorBtn) {
      authorBtn.disabled = getCheckedValues('#authorTable').length === 0;
    }
    if (publisherBtn) {
      publisherBtn.disabled = getCheckedValues('#publisherTable').length === 0;
    }
  }

  function toggleMaster(masterSel, rowSel) {
    const master = document.querySelector(masterSel);
    const rows = document.querySelectorAll(rowSel);

    if (!master) {
      return;
    }

    master.addEventListener('change', () => {
      rows.forEach(cb => {
        cb.checked = master.checked;
      });
      master.indeterminate = false;
      updateBulkButtons();
    });
  }

  function clearFormErrors(form) {
    if (!form) {
      return;
    }
    form.querySelectorAll('.is-invalid').forEach(el => {
      el.classList.remove('is-invalid');
      el.removeAttribute('aria-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => {
      el.classList.remove('d-block');
      el.classList.add('d-none');
      el.style.display = '';
    });
  }

  function wireSidebarNav() {
    document.querySelectorAll('.admin-catalog-nav-link').forEach(a => {
      a.addEventListener('click', (e) => {
        const tab = a.dataset.catalogNav;
        if (!['category', 'author', 'publisher'].includes(tab)) {
          return;
        }
        e.preventDefault();
        setTabInURL(tab, false);
        showPane(tab);
        setRightNavActive(tab);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    wireSidebarNav();

    const initial = getTabFromURL();
    setTabInURL(initial, true);
    showPane(initial);
    setRightNavActive(initial);

    window.addEventListener('popstate', () => {
      const tab = getTabFromURL();
      showPane(tab);
      setRightNavActive(tab);
    });

    toggleMaster('#cat_check_all', '#categoryTable tbody .cat-row-checkbox');
    toggleMaster('#author_check_all', '#authorTable tbody .author-row-checkbox');
    toggleMaster('#publisher_check_all', '#publisherTable tbody .publisher-row-checkbox');

    document.querySelector('#categoryTable')?.addEventListener('change', (e) => {
      if (e.target.classList?.contains('cat-row-checkbox')) {
        updateBulkButtons();
      }
    });
    document.querySelector('#authorTable')?.addEventListener('change', (e) => {
      if (e.target.classList?.contains('author-row-checkbox')) {
        updateBulkButtons();
      }
    });
    document.querySelector('#publisherTable')?.addEventListener('change', (e) => {
      if (e.target.classList?.contains('publisher-row-checkbox')) {
        updateBulkButtons();
      }
    });

    updateBulkButtons();

    document.getElementById('catBtnBulkDelete')?.addEventListener('click', async () => {
      const ids = getCheckedValues('#categoryTable');
      if (!ids.length) {
        return;
      }

      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: `Bạn sắp xoá <b>${ids.length}</b> category.`
      }) : Promise.resolve(confirm('Xoá các category đã chọn?')));

      if (!ok) {
        return;
      }

      const form = document.getElementById('catBulkForm');
      const box = document.getElementById('catBulkIds');
      makeHiddenInputs(box, 'ids[]', ids);
      form.submit();
    });

    document.querySelectorAll('.btnCateDelete').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = btn.closest('form');

        const ok = await (window.UIConfirm ? UIConfirm({
          title: 'Xác nhận xoá',
          message: 'Bạn có chắc chắn muốn xoá category này?'
        }) : Promise.resolve(confirm('Xoá category này?')));

        if (ok) {
          form.submit();
        }
      });
    });

    document.getElementById('authorBtnBulkDelete')?.addEventListener('click', async () => {
      const ids = getCheckedValues('#authorTable');
      if (!ids.length) {
        return;
      }

      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: `Bạn sắp xoá <b>${ids.length}</b> tác giả.`
      }) : Promise.resolve(confirm('Xoá các tác giả đã chọn?')));

      if (!ok) {
        return;
      }

      const form = document.getElementById('authorBulkForm');
      const box = document.getElementById('authorBulkIds');
      makeHiddenInputs(box, 'ids[]', ids);
      form.submit();
    });

    document.querySelectorAll('.btnauthorDelete').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = btn.closest('form');

        const ok = await (window.UIConfirm ? UIConfirm({
          title: 'Xác nhận xoá',
          message: 'Bạn có chắc chắn muốn xoá tác giả này?'
        }) : Promise.resolve(confirm('Xoá tác giả này?')));

        if (ok) {
          form.submit();
        }
      });
    });

    document.getElementById('publisherBtnBulkDelete')?.addEventListener('click', async () => {
      const ids = getCheckedValues('#publisherTable');
      if (!ids.length) {
        return;
      }

      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: `Bạn sắp xoá <b>${ids.length}</b> NXB.`
      }) : Promise.resolve(confirm('Xoá các NXB đã chọn?')));

      if (!ok) {
        return;
      }

      const form = document.getElementById('publisherBulkForm');
      const box = document.getElementById('publisherBulkIds');
      makeHiddenInputs(box, 'ids[]', ids);
      form.submit();
    });

    document.querySelectorAll('.btnPublisherDelete').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = btn.closest('form');

        const ok = await (window.UIConfirm ? UIConfirm({
          title: 'Xác nhận xoá',
          message: 'Bạn có chắc chắn muốn xoá NXB này?'
        }) : Promise.resolve(confirm('Xoá NXB này?')));

        if (ok) {
          form.submit();
        }
      });
    });

    const catModal = document.getElementById('uiCategoryModal');
    const catForm = document.getElementById('uiCategoryForm');
    const catTitle = document.getElementById('catModalTitle');

    function resetCategoryForm() {
      clearFormErrors(catForm);
      if (!catForm) {
        return;
      }

      catForm.querySelector('[name="__mode"]').value = 'create';
      catForm.action = catForm.dataset.storeUrl;
      catForm.querySelector('[name=_method]').value = 'POST';
      catForm.querySelector('[name="__form"]').value = 'category';

      const upd = catForm.querySelector('[name="__update_action"]');
      if (upd) {
        upd.value = '';
      }
      const img = catForm.querySelector('[name="__image"]');
      if (img) {
        img.value = '';
      }

      const n = catForm.querySelector('#cat_name');
      if (n) {
        n.value = '';
      }
      const s = catForm.querySelector('#cat_slug');
      if (s) {
        s.value = '';
      }
      const d = catForm.querySelector('#cat_description');
      if (d) {
        d.value = '';
      }
      const st = catForm.querySelector('#cat_status');
      if (st) {
        st.value = 'ACTIVE';
        if (window.jQuery && $.fn?.select2) {
          $('#cat_status').trigger('change.select2');
        }
      }

      if (catTitle) {
        catTitle.textContent = 'Thêm danh mục';
      }
    }

    document.querySelectorAll('[data-bs-target="#uiCategoryModal"]').forEach(btn => {
      btn.addEventListener('click', () => {
        resetCategoryForm();
      });
    });

    catModal?.addEventListener('hidden.bs.modal', () => {
      resetCategoryForm();
    });

    document.querySelectorAll('.btnCateEdit').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!catForm) {
          return;
        }

        clearFormErrors(catForm);

        catForm.querySelector('[name="__mode"]').value = 'edit';
        catForm.action = btn.dataset.updateUrl;
        catForm.querySelector('[name=_method]').value = 'PUT';
        catForm.querySelector('[name="__form"]').value = 'category';

        const upd = catForm.querySelector('[name="__update_action"]');
        if (upd) {
          upd.value = btn.dataset.updateUrl || '';
        }
        const img = catForm.querySelector('[name="__image"]');
        if (img) {
          img.value = '';
        }

        const n = catForm.querySelector('#cat_name');
        if (n) {
          n.value = btn.dataset.name || '';
        }
        const s = catForm.querySelector('#cat_slug');
        if (s) {
          s.value = btn.dataset.slug || '';
        }
        const d = catForm.querySelector('#cat_description');
        if (d) {
          d.value = btn.dataset.description || '';
        }
        const st = catForm.querySelector('#cat_status');
        if (st) {
          st.value = btn.dataset.status || 'ACTIVE';
          if (window.jQuery && $.fn?.select2) {
            $('#cat_status').trigger('change.select2');
          }
        }

        if (catTitle) {
          catTitle.textContent = 'Cập nhật category';
        }

        bootstrap.Modal.getOrCreateInstance(catModal).show();
      });
    });

    const authorModal = document.getElementById('uiAuthorModal');
    const authorForm = document.getElementById('uiAuthorForm');
    const authorImg = document.getElementById('author_image');
    const authorPrev = document.getElementById('author_image_preview');
    const authorPH = document.getElementById('author_image_placeholder');
    const authorTitle = document.getElementById('authorModalTitle');

    function setAuthorPreview(src) {
      if (!authorPrev || !authorPH) {
        return;
      }
      if (src) {
        authorPrev.src = src;
        authorPrev.classList.remove('d-none');
        authorPH.classList.add('d-none');
        return;
      }
      authorPrev.src = '';
      authorPrev.classList.add('d-none');
      authorPH.classList.remove('d-none');
    }

    function resetAuthorForm() {
      clearFormErrors(authorForm);
      if (!authorForm) {
        return;
      }

      authorForm.querySelector('[name="__mode"]').value = 'create';
      authorForm.action = authorForm.dataset.storeUrl;
      authorForm.querySelector('[name=_method]').value = 'POST';
      authorForm.querySelector('[name="__form"]').value = 'author';

      const upd = authorForm.querySelector('[name="__update_action"]');
      if (upd) {
        upd.value = '';
      }
      const img = authorForm.querySelector('[name="__image"]');
      if (img) {
        img.value = '';
      }

      const n = authorForm.querySelector('#author_name');
      if (n) {
        n.value = '';
      }
      const s = authorForm.querySelector('#author_slug');
      if (s) {
        s.value = '';
      }
      const d = authorForm.querySelector('#author_description');
      if (d) {
        d.value = '';
      }
      const st = authorForm.querySelector('#author_status');
      if (st) {
        st.value = 'ACTIVE';
        if (window.jQuery && $.fn?.select2) {
          $('#author_status').trigger('change.select2');
        }
      }

      try {
        if (authorImg) {
          authorImg.value = '';
        }
      } catch (_) {}

      setAuthorPreview('');

      if (authorTitle) {
        authorTitle.textContent = 'Thêm tác giả';
      }
    }

    document.querySelectorAll('[data-bs-target="#uiAuthorModal"]').forEach(btn => {
      btn.addEventListener('click', () => {
        resetAuthorForm();
      });
    });

    authorModal?.addEventListener('hidden.bs.modal', () => {
      resetAuthorForm();
    });

    document.querySelectorAll('.btnauthorEdit').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!authorForm) {
          return;
        }

        clearFormErrors(authorForm);

        authorForm.querySelector('[name="__mode"]').value = 'edit';
        authorForm.action = btn.dataset.updateUrl;
        authorForm.querySelector('[name=_method]').value = 'PUT';
        authorForm.querySelector('[name="__form"]').value = 'author';

        const upd = authorForm.querySelector('[name="__update_action"]');
        if (upd) {
          upd.value = btn.dataset.updateUrl || '';
        }
        const img = authorForm.querySelector('[name="__image"]');
        if (img) {
          img.value = btn.dataset.image || '';
        }

        const n = authorForm.querySelector('#author_name');
        if (n) {
          n.value = btn.dataset.name || '';
        }
        const s = authorForm.querySelector('#author_slug');
        if (s) {
          s.value = btn.dataset.slug || '';
        }
        const d = authorForm.querySelector('#author_description');
        if (d) {
          d.value = btn.dataset.description || '';
        }
        const st = authorForm.querySelector('#author_status');
        if (st) {
          st.value = btn.dataset.status || 'ACTIVE';
          if (window.jQuery && $.fn?.select2) {
            $('#author_status').trigger('change.select2');
          }
        }

        setAuthorPreview(btn.dataset.image || '');

        try {
          if (authorImg) {
            authorImg.value = '';
          }
        } catch (_) {}

        if (authorTitle) {
          authorTitle.textContent = 'Cập nhật tác giả';
        }

        bootstrap.Modal.getOrCreateInstance(authorModal).show();
      });
    });

    authorImg?.addEventListener('change', () => {
      const f = authorImg.files?.[0];
      setAuthorPreview(f ? URL.createObjectURL(f) : '');
    });

    const publisherModal = document.getElementById('uiPublisherModal');
    const publisherForm = document.getElementById('uiPublisherForm');
    const publisherLogo = document.getElementById('publisher_logo');
    const publisherPrev = document.getElementById('publisher_logo_preview');
    const publisherPH = document.getElementById('publisher_logo_placeholder');
    const publisherTitle = document.getElementById('publisherModalTitle');

    function setPublisherPreview(src) {
      if (!publisherPrev || !publisherPH) {
        return;
      }
      if (src) {
        publisherPrev.src = src;
        publisherPrev.classList.remove('d-none');
        publisherPH.classList.add('d-none');
        return;
      }
      publisherPrev.src = '';
      publisherPrev.classList.add('d-none');
      publisherPH.classList.remove('d-none');
    }

    function resetPublisherForm() {
      clearFormErrors(publisherForm);
      if (!publisherForm) {
        return;
      }

      publisherForm.querySelector('[name="__mode"]').value = 'create';
      publisherForm.action = publisherForm.dataset.storeUrl;
      publisherForm.querySelector('[name=_method]').value = 'POST';
      publisherForm.querySelector('[name="__form"]').value = 'publisher';

      const upd = publisherForm.querySelector('[name="__update_action"]');
      if (upd) {
        upd.value = '';
      }
      const img = publisherForm.querySelector('[name="__image"]');
      if (img) {
        img.value = '';
      }

      const n = publisherForm.querySelector('#publisher_name');
      if (n) {
        n.value = '';
      }
      const s = publisherForm.querySelector('#publisher_slug');
      if (s) {
        s.value = '';
      }
      const d = publisherForm.querySelector('#publisher_description');
      if (d) {
        d.value = '';
      }
      const st = publisherForm.querySelector('#publisher_status');
      if (st) {
        st.value = 'ACTIVE';
        if (window.jQuery && $.fn?.select2) {
          $('#publisher_status').trigger('change.select2');
        }
      }

      try {
        if (publisherLogo) {
          publisherLogo.value = '';
        }
      } catch (_) {}

      setPublisherPreview('');

      if (publisherTitle) {
        publisherTitle.textContent = 'Thêm NXB';
      }
    }

    document.querySelectorAll('[data-bs-target="#uiPublisherModal"]').forEach(btn => {
      btn.addEventListener('click', () => {
        resetPublisherForm();
      });
    });

    publisherModal?.addEventListener('hidden.bs.modal', () => {
      resetPublisherForm();
    });

    document.querySelectorAll('.btnPublisherEdit').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!publisherForm) {
          return;
        }

        clearFormErrors(publisherForm);

        publisherForm.querySelector('[name="__mode"]').value = 'edit';
        publisherForm.action = btn.dataset.updateUrl;
        publisherForm.querySelector('[name=_method]').value = 'PUT';
        publisherForm.querySelector('[name="__form"]').value = 'publisher';

        const upd = publisherForm.querySelector('[name="__update_action"]');
        if (upd) {
          upd.value = btn.dataset.updateUrl || '';
        }
        const img = publisherForm.querySelector('[name="__image"]');
        if (img) {
          img.value = btn.dataset.image || '';
        }

        const n = publisherForm.querySelector('#publisher_name');
        if (n) {
          n.value = btn.dataset.name || '';
        }
        const s = publisherForm.querySelector('#publisher_slug');
        if (s) {
          s.value = btn.dataset.slug || '';
        }
        const d = publisherForm.querySelector('#publisher_description');
        if (d) {
          d.value = btn.dataset.description || '';
        }
        const st = publisherForm.querySelector('#publisher_status');
        if (st) {
          st.value = btn.dataset.status || 'ACTIVE';
          if (window.jQuery && $.fn?.select2) {
            $('#publisher_status').trigger('change.select2');
          }
        }

        setPublisherPreview(btn.dataset.image || '');

        try {
          if (publisherLogo) {
            publisherLogo.value = '';
          }
        } catch (_) {}

        if (publisherTitle) {
          publisherTitle.textContent = 'Cập nhật NXB';
        }

        bootstrap.Modal.getOrCreateInstance(publisherModal).show();
      });
    });

    publisherLogo?.addEventListener('change', () => {
      const f = publisherLogo.files?.[0];
      setPublisherPreview(f ? URL.createObjectURL(f) : '');
    });

    const __stateEl = document.getElementById('__formState');
    const __hasErrors = __stateEl?.dataset.hasErrors === '1';
    const __which = (__stateEl?.dataset.which || null);
    const __mode = (__stateEl?.dataset.mode || 'create');
    const __updateAction = __stateEl?.dataset.updateAction || '';
    const __image = __stateEl?.dataset.image || '';

    if (__hasErrors) {
      if (__which === 'category' && catForm && catModal) {
        setTabInURL('category', true);
        showPane('category');
        setRightNavActive('category');

        catForm.querySelector('[name="__form"]').value = 'category';

        if (__mode === 'edit' && __updateAction) {
          catForm.action = __updateAction;
          catForm.querySelector('[name=_method]').value = 'PUT';
          if (catTitle) {
            catTitle.textContent = 'Cập nhật category';
          }
        } else {
          catForm.action = catForm.dataset.storeUrl;
          catForm.querySelector('[name=_method]').value = 'POST';
          if (catTitle) {
            catTitle.textContent = 'Thêm danh mục';
          }
        }

        bootstrap.Modal.getOrCreateInstance(catModal).show();
      }

      if (__which === 'author' && authorForm && authorModal) {
        setTabInURL('author', true);
        showPane('author');
        setRightNavActive('author');

        authorForm.querySelector('[name="__form"]').value = 'author';

        if (__mode === 'edit' && __updateAction) {
          authorForm.action = __updateAction;
          authorForm.querySelector('[name=_method]').value = 'PUT';
          if (authorTitle) {
            authorTitle.textContent = 'Cập nhật tác giả';
          }
        } else {
          authorForm.action = authorForm.dataset.storeUrl;
          authorForm.querySelector('[name=_method]').value = 'POST';
          if (authorTitle) {
            authorTitle.textContent = 'Thêm tác giả';
          }
        }

        setAuthorPreview(__image || '');
        bootstrap.Modal.getOrCreateInstance(authorModal).show();
      }

      if (__which === 'publisher' && publisherForm && publisherModal) {
        setTabInURL('publisher', true);
        showPane('publisher');
        setRightNavActive('publisher');

        publisherForm.querySelector('[name="__form"]').value = 'publisher';

        if (__mode === 'edit' && __updateAction) {
          publisherForm.action = __updateAction;
          publisherForm.querySelector('[name=_method]').value = 'PUT';
          if (publisherTitle) {
            publisherTitle.textContent = 'Cập nhật NXB';
          }
        } else {
          publisherForm.action = publisherForm.dataset.storeUrl;
          publisherForm.querySelector('[name=_method]').value = 'POST';
          if (publisherTitle) {
            publisherTitle.textContent = 'Thêm NXB';
          }
        }

        setPublisherPreview(__image || '');
        bootstrap.Modal.getOrCreateInstance(publisherModal).show();
      }
    }
  });
})();
