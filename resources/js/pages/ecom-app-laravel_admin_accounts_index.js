// Generated from inline <script> blocks in: ecom-app-laravel/resources/views/admin/accounts/index.blade.php
// Each section preserves original order and approximate line ranges.

/* ===== BEGIN inline script #1 (lines 407-696) ===== */
// @ts-nocheck
  document.addEventListener('DOMContentLoaded', function() {
    // ===== Refs
    const table = document.getElementById('accountTable');
    const master = document.getElementById('check_all');
    const btnOpen = document.getElementById('btnBulkOpen');
    const select = document.getElementById('bulk_status');

    const bulkForm = document.getElementById('bulkForm');
    const bulkStatusInput = document.getElementById('bulk_status_input');
    const bulkIdsContainer = document.getElementById('bulk_ids_container');

    const modalEl = document.getElementById('uiAccountEditModal');
    const formEl = document.getElementById('uiAccountEditForm');
    const imgPrev = document.getElementById('ac_avatar_preview');
    const fileInput = document.getElementById('ac_avatar');
    const statusSel = document.getElementById('ac_status');

    const tokensBox = document.getElementById('ac_roles_tokens');
    const suggestEl = document.getElementById('ac_roles_suggest');
    const hiddenBox = document.getElementById('ac_roles_inputs');

    const ROLES = Array.isArray(window.ROLES_MASTER) ? window.ROLES_MASTER.map(r => ({
      id: String(r.id),
      name: r.name
    })) : [];
    const AVATAR_PH = typeof window.AVATAR_BASE === 'string' ? window.AVATAR_BASE : '';

    // ===== Helpers: table
    function getRowCheckboxes() {
      return table ? Array.from(table.querySelectorAll('tbody .row-checkbox')) : [];
    }

    function markRow(cb) {
      const tr = cb ? cb.closest('tr') : null;
      if (!tr) return;
      if (cb.checked) tr.classList.add('row-checked');
      else tr.classList.remove('row-checked');
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

    function getCheckedIds() {
      return getRowCheckboxes().filter(x => x.checked).map(x => x.value);
    }

    function statusText(v) {
      if (v === 'ACTIVE') return 'Kích hoạt';
      if (v === 'BAN') return 'Khoá';
      return '—';
    }

    function normStatus(s) {
      s = String(s || '').toUpperCase().trim();
      return s === 'BAN' ? 'BAN' : 'ACTIVE';
    }

    // ===== Events: table
    if (table) {
      table.addEventListener('change', function(e) {
        const t = e.target;
        if (!t.classList || !t.classList.contains('row-checkbox')) return;
        markRow(t);
        refreshMaster();
      });
      table.addEventListener('click', function(e) {
        const td = e.target.closest('td');
        if (!td) return;
        if (td.cellIndex !== 0) return;
        if (e.target.tagName === 'INPUT') return;
        const cb = td.querySelector('.row-checkbox');
        if (!cb) return;
        cb.checked = !cb.checked;
        markRow(cb);
        refreshMaster();
      });
    }
    if (master) {
      master.addEventListener('change', function() {
        const cbs = getRowCheckboxes();
        for (const cb of cbs) {
          cb.checked = master.checked;
          markRow(cb);
        }
        master.indeterminate = false;
        refreshMaster();
      });
    }
    getRowCheckboxes().forEach(markRow);
    refreshMaster();

    // ===== Confirm fallback
    function stripHtml(html) {
      const d = document.createElement('div');
      d.innerHTML = html;
      return d.textContent || d.innerText || '';
    }
    async function confirmDialog(opts) {
      if (typeof window.UIConfirm === 'function') return await window.UIConfirm(opts);
      const title = (opts && opts.title) || 'Xác nhận';
      const msg = (opts && opts.message) || 'Bạn có chắc không?';
      return window.confirm(title + '\n\n' + stripHtml(msg));
    }

    // ===== Bulk submit
    if (btnOpen) {
      btnOpen.addEventListener('click', async function() {
        const ids = getCheckedIds();
        const val = select && select.value ? select.value : '';

        if (!ids.length) {
          await confirmDialog({
            title: 'Thiếu lựa chọn',
            message: 'Vui lòng chọn ít nhất <b>1</b> tài khoản.'
          });
          return;
        }
        if (val !== 'ACTIVE' && val !== 'BAN') {
          await confirmDialog({
            title: 'Chưa chọn trạng thái',
            message: 'Vui lòng chọn trạng thái đích.'
          });
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
          i.type = 'hidden';
          i.name = 'ids[]';
          i.value = id;
          bulkIdsContainer.appendChild(i);
        }
        bulkForm.submit();
      });
    }

    // ===== Avatar preview
    if (fileInput && imgPrev) {
      fileInput.addEventListener('change', function() {
        const f = fileInput.files && fileInput.files[0];
        if (!f) return;
        imgPrev.src = URL.createObjectURL(f);
      });
    }

    // ===== Roles pick-only
    let selected = [];

    function setSelected(ids) {
      selected = Array.from(new Set((Array.isArray(ids) ? ids : []).filter(Boolean).map(String)));
      renderRoles();
    }

    function addById(id) {
      id = String(id);
      if (!id || selected.includes(id)) return;
      selected.push(id);
      renderRoles();
    }

    function removeById(id) {
      id = String(id);
      selected = selected.filter(x => x !== id);
      renderRoles();
    }

    function remainingRoles() {
      return ROLES.filter(r => !selected.includes(String(r.id)));
    }

    function renderRoles() {
      if (!tokensBox || !suggestEl || !hiddenBox) return;

      hiddenBox.innerHTML = '';
      for (const id of selected) {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = 'role_ids[]';
        i.value = id;
        hiddenBox.appendChild(i);
      }

      const tokens = selected.map(id => {
        const role = ROLES.find(r => String(r.id) === String(id));
        if (!role) return '';
        return '<span class="ac-tag" data-id="' + id + '">' +
          '<span>' + role.name + '</span>' +
          '<button class="ac-x" type="button" aria-label="Xóa" data-x="' + id + '">×</button>' +
          '</span>';
      }).join('');
      tokensBox.innerHTML = tokens;
      tokensBox.classList.toggle('is-empty', selected.length === 0);

      const list = remainingRoles();
      suggestEl.innerHTML = list.map(r => '<span class="sg" data-add="' + r.id + '"><span class="plus">＋</span><span>' + r.name + '</span></span>').join('');
    }
    if (tokensBox) {
      tokensBox.addEventListener('click', function(e) {
        const id = e.target && e.target.getAttribute && e.target.getAttribute('data-x');
        if (id) removeById(id);
      });
    }
    if (suggestEl) {
      suggestEl.addEventListener('click', function(e) {
        const chip = e.target.closest && e.target.closest('[data-add]');
        if (!chip) return;
        addById(chip.getAttribute('data-add'));
      });
    }

    // ===== Fill form + open modal
    function fillFormFromBtn(btn) {
      if (!btn || !formEl) return;

      const url = btn.getAttribute('data-update-url') || '';
      const fullName = btn.getAttribute('data-full_name') || '';
      const email = btn.getAttribute('data-email') || '';
      const phone = btn.getAttribute('data-phone') || '';
      const address = btn.getAttribute('data-address') || '';
      const status = normStatus(btn.getAttribute('data-status'));
      const avatar = btn.getAttribute('data-avatar') || '';
      const roleIdsCS = (btn.getAttribute('data-role_ids') || '').trim();
      const roleIds = roleIdsCS ? roleIdsCS.split(',').map(s => s.trim()).filter(Boolean) : [];

      formEl.action = url;
      const nameEl = formEl.querySelector('#ac_full_name');
      const mailEl = formEl.querySelector('#ac_email');
      const phEl = formEl.querySelector('#ac_phone');
      const addrEl = formEl.querySelector('#ac_address');
      if (nameEl) nameEl.value = fullName;
      if (mailEl) mailEl.value = email;
      if (phEl) phEl.value = phone;
      if (addrEl) addrEl.value = address;

      if (imgPrev) imgPrev.src = avatar || AVATAR_PH;
      if (fileInput) {
        try {
          fileInput.value = '';
        } catch (_) {}
      }

      if (statusSel) {
        for (const o of statusSel.options) {
          if (!o.value) o.selected = false;
        }
        statusSel.value = status;
        if (window.jQuery && $.fn && $.fn.select2) $('#ac_status').trigger('change.select2');
      }

      setSelected(roleIds);
    }

    function openModal(el) {
      if (!el) return;
      if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getOrCreateInstance(el);
        inst.show();
      }
    }
    document.addEventListener('click', function(e) {
      const btn = e.target.closest ? e.target.closest('.btnAccountEdit') : null;
      if (!btn) return;
      fillFormFromBtn(btn);
      openModal(modalEl);
    });

    // initial render for roles box
    renderRoles();
  });
/* ===== END inline script #1 ===== */

/* ===== BEGIN inline script #2 (lines 698-737) ===== */
function getTabFromURL(){
  const u = new URL(location.href);
  const t = u.searchParams.get('tab');
  return ['accounts','roles','stats'].includes(t) ? t : 'accounts';
}
function setTabInURL(tab, replace=false){
  const u = new URL(location.href);
  u.searchParams.set('tab', tab);
  replace ? history.replaceState(null,'',u) : history.pushState(null,'',u);
}
function showTab(tab){
  const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
  if (!trigger) return;
  new bootstrap.Tab(trigger).show();
}

document.addEventListener('DOMContentLoaded', () => {
  // mở đúng tab theo URL (mặc định 'accounts')
  showTab(getTabFromURL());

  // khi đổi tab thì cập nhật ?tab=
  const tabs = document.getElementById('accountTabs');
  if (tabs) {
    tabs.addEventListener('shown.bs.tab', (e) => {
      const pane = e.target.getAttribute('data-bs-target'); // '#roles-pane' ...
      let tab = 'accounts';
      if (pane.includes('roles')) tab = 'roles';
      else if (pane.includes('stats')) tab = 'stats';
      setTabInURL(tab);
    });
  }

  // hỗ trợ nút Back/Forward
  window.addEventListener('popstate', () => showTab(getTabFromURL()));

  // lần đầu: nếu chưa có ?tab= thì set theo tab hiện tại (không đẩy history)
  setTabInURL(getTabFromURL(), true);
});
/* ===== END inline script #2 ===== */

/* ===== BEGIN inline script #4 (lines 747-751) ===== */
const el = document.getElementById('seed');
  window.ROLES_MASTER = JSON.parse(el.dataset.roles);
  window.AVATAR_BASE = JSON.parse(el.dataset.avatar);

