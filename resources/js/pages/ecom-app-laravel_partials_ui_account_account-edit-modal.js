// @ts-nocheck
document.addEventListener('DOMContentLoaded', function() {
  // ===== Refs
  const modalEl  = document.getElementById('uiAccountEditModal');
  const formEl   = document.getElementById('uiAccountEditForm');
  const imgPrev  = document.getElementById('ac_avatar_preview');
  const fileInp  = document.getElementById('ac_avatar');
  const statusEl = document.getElementById('ac_status');

  const tokensBox = document.getElementById('ac_roles_tokens');
  const suggestEl = document.getElementById('ac_roles_suggest');
  const hiddenBox = document.getElementById('ac_roles_inputs');

  const stateEl   = document.getElementById('__accountFormState');
  const updInput  = document.getElementById('__update_action');
  const oldRoles  = document.getElementById('__old_role_ids');

  // ===== Roles master seed (đặt từ blade khác)
  const ROLES = Array.isArray(window.ROLES_MASTER) ? window.ROLES_MASTER.map(r => ({ id: String(r.id), name: r.name })) : [];

  // ===== Utilities
  function refreshSelect2() {
    if (window.jQuery && $.fn && $.fn.select2) {
      $('#ac_status').trigger('change.select2');
    }
  }

  // ===== Avatar preview when choose file
  if (fileInp && imgPrev) {
    fileInp.addEventListener('change', function() {
      const f = fileInp.files && fileInp.files[0];
      if (f) imgPrev.src = URL.createObjectURL(f);
    });
  }

  // ===== Roles UI
  let selected = [];
  function setSelected(ids) {
    selected = Array.from(new Set((Array.isArray(ids) ? ids : []).filter(Boolean).map(String)));
    renderRoles();
  }
  function addById(id)  { id = String(id); if (!id || selected.includes(id)) return; selected.push(id); renderRoles(); }
  function removeById(id){ id = String(id); selected = selected.filter(x => x !== id); renderRoles(); }
  function remainingRoles(){ return ROLES.filter(r => !selected.includes(String(r.id))); }

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
      return '<span class="ac-tag" data-id="'+id+'"><span>'+role.name+'</span><button class="ac-x" type="button" aria-label="Xóa" data-x="'+id+'">×</button></span>';
    }).join('');
    tokensBox.innerHTML = tokens;
    tokensBox.classList.toggle('is-empty', selected.length === 0);

    const list = remainingRoles();
    suggestEl.innerHTML = list.map(r => '<span class="sg" data-add="'+r.id+'"><span class="plus">＋</span><span>'+r.name+'</span></span>').join('');
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
      if (chip) addById(chip.getAttribute('data-add'));
    });
  }

  // ===== Fill form from edit button
  function normStatus(s) {
    s = String(s || '').toUpperCase().trim();
    return s === 'BAN' ? 'BAN' : (s === 'ACTIVE' ? 'ACTIVE' : '');
  }
  function fillFormFromBtn(btn) {
    if (!btn || !formEl) return;

    const url     = btn.getAttribute('data-update-url') || '';
    const full    = btn.getAttribute('data-full_name') || '';
    const email   = btn.getAttribute('data-email') || '';
    const phone   = btn.getAttribute('data-phone') || '';
    const address = btn.getAttribute('data-address') || '';
    const status  = normStatus(btn.getAttribute('data-status'));
    const avatar  = btn.getAttribute('data-avatar') || '';
    const roleIds = (btn.getAttribute('data-role_ids') || '').split(',').map(s => s.trim()).filter(Boolean);

    formEl.action = url;
    (document.getElementById('ac_full_name')||{}).value = full;
    (document.getElementById('ac_email')||{}).value     = email;
    (document.getElementById('ac_phone')||{}).value     = phone;
    (document.getElementById('ac_address')||{}).value   = address;

    if (statusEl) {
      statusEl.value = status;
      refreshSelect2();
    }

    if (imgPrev) {
      imgPrev.src = avatar || imgPrev.src;
    }
    try { if (fileInp) fileInp.value = ''; } catch(_) {}

    setSelected(roleIds);
  }

  // ===== Click edit button to open modal
  document.addEventListener('click', function(e) {
    const btn = e.target.closest ? e.target.closest('.btnAccountEdit') : null;
    if (!btn) return;
    fillFormFromBtn(btn);
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });

  // ===== Auto open after validation fail (khôi phục avatar từ button, không cần server trả về)
  if (stateEl && stateEl.dataset.hasErrors === '1' && modalEl && formEl && updInput) {
    const updateUrl = updInput.value || '';
    if (updateUrl) {
      const btn = Array.from(document.querySelectorAll('.btnAccountEdit'))
        .find(b => b.getAttribute('data-update-url') === updateUrl);
      if (btn) {
        // Không ghi đè các input đã có old(); chỉ đặt avatar, action và roles UI
        formEl.action = updateUrl;

        // avatar: lấy từ data-avatar của button
        const avatar = btn.getAttribute('data-avatar') || '';
        if (imgPrev && avatar) imgPrev.src = avatar;

        // roles: lấy lại từ hidden old
        try {
          const arr = JSON.parse((oldRoles && oldRoles.value) ? oldRoles.value : '[]');
          setSelected(Array.isArray(arr) ? arr : []);
        } catch (_) { setSelected([]); }

        refreshSelect2();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    }
  }

  // initial render tokens
  renderRoles();
});
