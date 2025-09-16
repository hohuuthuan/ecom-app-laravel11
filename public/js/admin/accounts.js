// public/js/admin/accounts.js
(function () {
  'use strict';

  // ===== Seed từ Blade → biến global trước khi dùng =====
  (function seedGlobals() {
    const s = document.getElementById('seed');
    try { window.ROLES_MASTER = s ? JSON.parse(s.dataset.roles) : []; } catch (_) { window.ROLES_MASTER = []; }
    try { window.AVATAR_PLACEHOLDER = s ? JSON.parse(s.dataset.avatar) : ''; } catch (_) { window.AVATAR_PLACEHOLDER = ''; }
  })();

  // ===== Helpers chung =====
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }
  function stripHtml(html) { const d = document.createElement('div'); d.innerHTML = html; return d.textContent || d.innerText || ''; }
  async function confirmDialog(opts) {
    if (typeof window.UIConfirm === 'function') return await window.UIConfirm(opts);
    const title = (opts && opts.title) || 'Xác nhận';
    const msg = (opts && opts.message) || 'Bạn có chắc không?';
    return window.confirm(title + '\n\n' + stripHtml(msg));
  }
  function statusText(v) {
    const t = String(v || '').toUpperCase();
    if (t === 'ACTIVE') return 'Kích hoạt';
    if (t === 'BAN' || t === 'INACTIVE') return 'Khoá';
    return '—';
  }
  // Bulk: BE dùng INACTIVE thay cho BAN
  function toServerStatus(v) {
    const t = String(v || '').toUpperCase().trim();
    return t === 'BAN' ? 'INACTIVE' : t;
  }

  // ===== Select2 theo quy ước =====
  function initSelect2() {
    if (window.jQuery && $.fn && $.fn.select2) {
      $('.setupSelect2') && $('.setupSelect2').select2 && $('.setupSelect2').select2();
    }
  }

  // ===== Table chọn nhiều =====
  function initBulkSelection() {
    const table = $('#accountTable');
    const master = $('#check_all');

    function getRowCheckboxes() { return table ? $all('tbody .row-checkbox', table) : []; }
    function markRow(cb) {
      const tr = cb ? cb.closest('tr') : null;
      if (!tr) return;
      if (cb.checked) tr.classList.add('row-checked'); else tr.classList.remove('row-checked');
      tr.classList.remove('table-active');
    }
    function refreshMaster() {
      if (!master) return;
      const cbs = getRowCheckboxes();
      const total = cbs.length;
      const checked = cbs.filter(x => x.checked).length;
      master.indeterminate = false;
      master.checked = total > 0 && checked === total;
    }

    if (table) {
      table.addEventListener('change', function (e) {
        const t = e.target;
        if (!t.classList || !t.classList.contains('row-checkbox')) return;
        markRow(t); refreshMaster();
      });
      table.addEventListener('click', function (e) {
        const td = e.target.closest('td');
        if (!td) return;
        if (td.cellIndex !== 0) return;
        if (e.target.tagName === 'INPUT') return;
        const cb = td.querySelector('.row-checkbox');
        if (!cb) return;
        cb.checked = !cb.checked;
        markRow(cb); refreshMaster();
      });
      getRowCheckboxes().forEach(markRow);
      refreshMaster();
    }

    if (master) {
      master.addEventListener('change', function () {
        const cbs = getRowCheckboxes();
        for (const cb of cbs) { cb.checked = master.checked; markRow(cb); }
        master.indeterminate = false;
        refreshMaster();
      });
    }
  }

  // ===== Bulk submit =====
  function initBulkSubmit() {
    const table = $('#accountTable');
    const btnOpen = $('#btnBulkOpen');
    const select = $('#bulk_status');
    const bulkForm = $('#bulkForm');
    const bulkStatusInput = $('#bulk_status_input');
    const bulkIdsContainer = $('#bulk_ids_container');

    function getCheckedIds() {
      if (!table) return [];
      return $all('tbody .row-checkbox:checked', table).map(x => x.value);
    }

    if (btnOpen) {
      btnOpen.addEventListener('click', async function () {
        const ids = getCheckedIds();
        const raw = select && select.value ? select.value : '';
        const val = toServerStatus(raw);

        if (!ids.length) {
          await confirmDialog({ title: 'Thiếu lựa chọn', message: 'Vui lòng chọn ít nhất <b>1</b> tài khoản.' });
          return;
        }
        if (val !== 'ACTIVE' && val !== 'INACTIVE') {
          await confirmDialog({ title: 'Chưa chọn trạng thái', message: 'Vui lòng chọn trạng thái đích.' });
          return;
        }

        const ok = await confirmDialog({
          title: 'Xác nhận cập nhật',
          message: 'Bạn sắp cập nhật cho <b>' + ids.length + '</b> tài khoản.<br>Trạng thái: <span class="badge ' + (val === 'ACTIVE' ? 'bg-success' : 'bg-danger') + '">' + statusText(val) + '</span>',
          confirmText: 'Xác nhận',
          cancelText: 'Huỷ'
        });
        if (!ok) return;

        if (!bulkForm) return;
        bulkStatusInput.value = val;
        bulkIdsContainer.innerHTML = '';
        for (const id of ids) {
          const i = document.createElement('input');
          i.type = 'hidden'; i.name = 'ids[]'; i.value = id;
          bulkIdsContainer.appendChild(i);
        }
        bulkForm.submit();
      });
    }
  }

  // ===== Avatar preview (modal) =====
  function initAvatarPreview() {
    const imgPrev = $('#ac_avatar_preview');
    const fileInput = $('#ac_avatar');
    if (!fileInput || !imgPrev) return;
    fileInput.addEventListener('change', function () {
      const f = fileInput.files && fileInput.files[0];
      if (!f) return;
      imgPrev.src = URL.createObjectURL(f);
    });
  }

  // ===== Roles token UI (pick-only) =====
  function initRolesTokens() {
    const ROLES = (window.ROLES_MASTER || []).map(r => ({ id: String(r.id), name: r.name }));
    const tokensBox = $('#ac_roles_tokens');
    const suggestEl = $('#ac_roles_suggest');
    const hiddenBox = $('#ac_roles_inputs');
    let selected = [];

    function remainingRoles() { return ROLES.filter(r => !selected.includes(String(r.id))); }
    function renderRoles() {
      if (!tokensBox || !suggestEl || !hiddenBox) return;
      hiddenBox.innerHTML = '';
      for (const id of selected) {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = 'role_ids[]'; i.value = id;
        hiddenBox.appendChild(i);
      }
      const tokens = selected.map(id => {
        const role = ROLES.find(r => String(r.id) === String(id));
        if (!role) return '';
        return '<span class="ac-tag" data-id="' + id + '"><span>' + role.name + '</span><button class="ac-x" type="button" aria-label="Xóa" data-x="' + id + '">×</button></span>';
      }).join('');
      tokensBox.innerHTML = tokens;
      tokensBox.classList.toggle('is-empty', selected.length === 0);
      const list = remainingRoles();
      suggestEl.innerHTML = list.map(r => '<span class="sg" data-add="' + r.id + '"><span class="plus">＋</span><span>' + r.name + '</span></span>').join('');
    }
    function addById(id) {
      id = String(id);
      if (!id || selected.includes(id)) return;
      selected.push(id); renderRoles();
    }
    function removeById(id) {
      id = String(id);
      selected = selected.filter(x => x !== id); renderRoles();
    }
    function setSelected(ids) {
      selected = Array.from(new Set((Array.isArray(ids) ? ids : []).filter(Boolean).map(String)));
      renderRoles();
    }

    tokensBox && tokensBox.addEventListener('click', function (e) {
      const id = e.target && e.target.getAttribute && e.target.getAttribute('data-x');
      if (id) removeById(id);
    });
    suggestEl && suggestEl.addEventListener('click', function (e) {
      const chip = e.target.closest && e.target.closest('[data-add]');
      if (!chip) return;
      addById(chip.getAttribute('data-add'));
    });

    // expose cho initEditModal
    window.__AC_roles_setSelected = setSelected;
  }

  // ===== Edit Modal: fill + error bag + open =====
  function initEditModal() {
    const modalEl = $('#uiAccountEditModal');
    const formEl = $('#uiAccountEditForm');
    const imgPrev = $('#ac_avatar_preview');
    const fileInput = $('#ac_avatar');
    const statusSel = $('#ac_status');
    const keepAction = $('#__update_action');
    const AVATAR_PH = window.AVATAR_PLACEHOLDER || '';

    if (!modalEl || !formEl) return;

    function normStatus(s) {
      s = String(s || '').toUpperCase().trim();
      // Modal select có ACTIVE|BAN
      return s === 'BAN' ? 'BAN' : 'ACTIVE';
    }

    function clearErrors() {
      formEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      const rolesBox = $('#ac_roles_tokens');
      rolesBox && rolesBox.classList.remove('is-invalid');
      formEl.querySelectorAll('[data-err]').forEach(el => {
        el.textContent = '';
        el.classList.remove('d-block'); el.classList.add('d-none');
      });
      if (window.jQuery && $.fn?.select2) {
        $('#ac_status').siblings('.select2-container').find('.select2-selection').removeClass('select2-invalid');
      }
    }

    function applyErrorsBag(errors) {
      const rolesBox = $('#ac_roles_tokens');
      const bag = {};
      Object.entries(errors || {}).forEach(([k, arr]) => {
        const key = k.split('.')[0];
        if (!bag[key] && Array.isArray(arr) && arr[0]) bag[key] = arr[0];
      });

      function showErr(name, msg) {
        const target = formEl.querySelector(`[name="${name}"]`);
        const slot = formEl.querySelector(`[data-err="${name}"]`);
        if (name === 'role_ids') {
          rolesBox && rolesBox.classList.add('is-invalid');
        } else if (name === 'status' && window.jQuery && $.fn?.select2) {
          $('#ac_status').siblings('.select2-container').find('.select2-selection').addClass('select2-invalid');
        }
        if (target && name !== 'role_ids') target.classList.add('is-invalid');
        if (slot) {
          slot.textContent = msg;
          slot.classList.remove('d-none'); slot.classList.add('d-block');
        }
      }
      Object.entries(bag).forEach(([n, m]) => showErr(n, m));
    }

    function fillFormFromBtn(btn) {
      if (!btn || !formEl) return;

      const url = btn.getAttribute('data-update-url') || formEl.action;
      formEl.action = url;
      if (keepAction) keepAction.value = url;

      const name = btn.getAttribute('data-name') || '';
      const email = btn.getAttribute('data-email') || '';
      const phone = btn.getAttribute('data-phone') || '';
      const address = btn.getAttribute('data-address') || '';
      const status = normStatus(btn.getAttribute('data-status'));
      const avatar = btn.getAttribute('data-avatar') || '';
      const roleIdsCS = (btn.getAttribute('data-role_ids') || '').trim();
      const roleIds = roleIdsCS ? roleIdsCS.split(',').map(s => s.trim()).filter(Boolean) : [];

      const nameEl = formEl.querySelector('#ac_name');
      const mailEl = formEl.querySelector('#ac_email');
      const phEl = formEl.querySelector('#ac_phone');
      const addrEl = formEl.querySelector('#ac_address');
      if (nameEl) nameEl.value = name;
      if (mailEl) mailEl.value = email;
      if (phEl) phEl.value = phone;
      if (addrEl) addrEl.value = address;

      if (imgPrev) imgPrev.src = avatar || AVATAR_PH;
      if (fileInput) { try { fileInput.value = ''; } catch (_) { } }

      if (statusSel) {
        statusSel.value = status;
        if (window.jQuery && $.fn?.select2) $('#ac_status').trigger('change.select2');
      }

      if (typeof window.__AC_roles_setSelected === 'function') {
        window.__AC_roles_setSelected(roleIds);
      }
    }

    function openModal(el) {
      if (!el) return;
      if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getOrCreateInstance(el);
        inst.show();
      }
    }

    // Click list → open modal
    document.addEventListener('click', function (e) {
      const btn = e.target.closest && e.target.closest('.btnAccountEdit');
      if (!btn) return;
      clearErrors();
      fillFormFromBtn(btn);
      openModal(modalEl);
    });

    // Server trả lỗi → auto open + giữ state
    const errScript = document.getElementById('accountEditErrors');
    if (errScript) {
      try {
        const bag = JSON.parse(errScript.textContent || '{}');
        const oldAction = document.getElementById('__update_action')?.value || '';
        if (oldAction) {
          const btnMatch = document.querySelector(`.btnAccountEdit[data-update-url="${oldAction}"]`);
          const src = btnMatch ? (btnMatch.getAttribute('data-avatar') || AVATAR_PH) : (AVATAR_PH || '');
          if (imgPrev) imgPrev.src = src;
          if (fileInput) { try { fileInput.value = ''; } catch (_) { } }
          formEl.action = oldAction;
        }
        if (imgPrev && !imgPrev.src) imgPrev.src = AVATAR_PH;

        const raw = document.getElementById('__old_role_ids')?.value || '[]';
        const oldRoleIds = JSON.parse(raw);
        if (typeof window.__AC_roles_setSelected === 'function') {
          window.__AC_roles_setSelected(Array.isArray(oldRoleIds) ? oldRoleIds : []);
        }

        openModal(modalEl);
        if (window.jQuery && $.fn?.select2) $('#ac_status').trigger('change.select2');
        applyErrorsBag(bag);
      } catch (_) { }
    }

    modalEl.addEventListener('hidden.bs.modal', function () {
      clearErrors();
      document.getElementById('accountEditErrors')?.remove();
    });

    if (imgPrev && !imgPrev.src) imgPrev.src = AVATAR_PH;
  }

  // ===== Tabs (?tab=accounts|roles|stats) =====
  function initTabs() {
    function getTabFromURL() {
      const u = new URL(location.href);
      const t = u.searchParams.get('tab');
      return ['accounts', 'roles', 'stats'].includes(t) ? t : 'accounts';
    }
    function setTabInURL(tab, replace) {
      const u = new URL(location.href);
      u.searchParams.set('tab', tab);
      replace ? history.replaceState(null, '', u) : history.pushState(null, '', u);
    }
    function showTab(tab) {
      const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
      if (!trigger) return;
      new bootstrap.Tab(trigger).show();
    }

    document.addEventListener('DOMContentLoaded', () => {
      showTab(getTabFromURL());
      const tabs = document.getElementById('accountTabs');
      if (tabs) {
        tabs.addEventListener('shown.bs.tab', (e) => {
          const pane = e.target.getAttribute('data-bs-target');
          let tab = 'accounts';
          if (pane.includes('roles')) tab = 'roles';
          else if (pane.includes('stats')) tab = 'stats';
          setTabInURL(tab);
        });
      }
      window.addEventListener('popstate', () => showTab(getTabFromURL()));
      setTabInURL(getTabFromURL(), true);
    });
  }

  // ===== Boot =====
  document.addEventListener('DOMContentLoaded', function () {
    initSelect2();
    initBulkSelection();
    initBulkSubmit();
    initAvatarPreview();
    initRolesTokens();
    initEditModal();
    initTabs();
  });
})();
