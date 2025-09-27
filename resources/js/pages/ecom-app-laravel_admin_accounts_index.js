// @ts-nocheck
document.addEventListener('DOMContentLoaded', function() {
  // ===== Refs
  const table     = document.getElementById('accountTable');
  const master    = document.getElementById('check_all');
  const btnOpen   = document.getElementById('btnBulkOpen');
  const select    = document.getElementById('bulk_status');

  const bulkForm  = document.getElementById('bulkForm');
  const bulkStatI = document.getElementById('bulk_status_input');
  const bulkIdsCt = document.getElementById('bulk_ids_container');

  const modalEl   = document.getElementById('uiAccountEditModal');
  const formEl    = document.getElementById('uiAccountEditForm');
  const imgPrev   = document.getElementById('ac_avatar_preview');
  const fileInput = document.getElementById('ac_avatar');
  const statusSel = document.getElementById('ac_status');

  const tokensBox = document.getElementById('ac_roles_tokens');
  const suggestEl = document.getElementById('ac_roles_suggest');
  const hiddenBox = document.getElementById('ac_roles_inputs');

  const stateEl   = document.getElementById('__accountFormState');
  const updInput  = document.getElementById('__update_action');
  const oldRoles  = document.getElementById('__old_role_ids');

  // ===== Seed ROLES + avatar mặc định
  let ROLES = [];
  let AVATAR_PH = '';
  const seed = document.getElementById('seed');
  if (seed) {
    try { ROLES = JSON.parse(seed.dataset.roles || '[]'); } catch(_) { ROLES = []; }
    try { AVATAR_PH = JSON.parse(seed.dataset.avatar || '""'); } catch(_) { AVATAR_PH = ''; }
  }

  // ===== Helpers
  function stripHtml(html){ const d=document.createElement('div'); d.innerHTML=html; return d.textContent||d.innerText||''; }
  async function UIConfirmWrap(opts){
    if (typeof window.UIConfirm === 'function') return await window.UIConfirm(opts);
    const title=(opts&&opts.title)||'Xác nhận'; const msg=(opts&&opts.message)||'Bạn có chắc không?';
    return window.confirm(title+'\n\n'+stripHtml(msg));
  }
  function refreshSelect2(){ if (window.jQuery && $.fn && $.fn.select2) $('#ac_status').trigger('change.select2'); }
  function normStatus(s){ s=String(s||'').toUpperCase().trim(); return s==='BAN'?'BAN':(s==='ACTIVE'?'ACTIVE':''); }

  // ===== Bulk helpers
  function rowCbs(){ return table ? Array.from(table.querySelectorAll('tbody .row-checkbox')) : []; }
  function markRow(cb){ const tr=cb?cb.closest('tr'):null; if(!tr)return; if(cb.checked)tr.classList.add('row-checked'); else tr.classList.remove('row-checked'); tr.classList.remove('table-active'); }
  function refreshMaster(){ if(!master)return; const c=rowCbs(); const t=c.length, k=c.filter(x=>x.checked).length; master.indeterminate=false; master.checked=t>0 && k===t; }
  function checkedIds(){ return rowCbs().filter(x=>x.checked).map(x=>x.value); }

  // ===== Table events
  if (table) {
    table.addEventListener('change', (e) => {
      const t=e.target; if (!t.classList || !t.classList.contains('row-checkbox')) return;
      markRow(t); refreshMaster();
    });
    table.addEventListener('click', (e) => {
      const td=e.target.closest('td'); if(!td)return; if(td.cellIndex!==0)return; if(e.target.tagName==='INPUT')return;
      const cb=td.querySelector('.row-checkbox'); if(!cb)return; cb.checked=!cb.checked; markRow(cb); refreshMaster();
    });
  }
  if (master) {
    master.addEventListener('change', () => {
      for (const cb of rowCbs()) { cb.checked = master.checked; markRow(cb); }
      master.indeterminate=false; refreshMaster();
    });
  }
  rowCbs().forEach(markRow); refreshMaster();

  // ===== Bulk submit
  if (btnOpen) {
    btnOpen.addEventListener('click', async () => {
      const ids=checkedIds(); const val=select&&select.value?select.value:'';
      if(!ids.length){ await UIConfirmWrap({title:'Thiếu lựa chọn', message:'Vui lòng chọn ít nhất <b>1</b> tài khoản.'}); return; }
      if(val!=='ACTIVE'&&val!=='BAN'){ await UIConfirmWrap({title:'Chưa chọn trạng thái', message:'Vui lòng chọn trạng thái đích.'}); return; }
      const ok=await UIConfirmWrap({ title:'Xác nhận cập nhật', message:'Bạn sắp cập nhật cho <b>'+ids.length+'</b> tài khoản.<br>Trạng thái: <span class="badge '+(val==='ACTIVE'?'bg-success':'bg-danger')+'">'+val+'</span>'});
      if(!ok) return;
      if(!bulkForm) return;
      bulkStatI.value=val; bulkIdsCt.innerHTML='';
      for(const id of ids){ const i=document.createElement('input'); i.type='hidden'; i.name='ids[]'; i.value=id; bulkIdsCt.appendChild(i); }
      bulkForm.submit();
    });
  }

  // ===== Avatar preview when choose file
  if (fileInput && imgPrev) {
    fileInput.addEventListener('change', () => {
      const f=fileInput.files && fileInput.files[0]; if(!f) return;
      imgPrev.src = URL.createObjectURL(f);
    });
  }

  // ===== Roles UI (chips)
  let selected=[];
  function setSelected(ids){ selected=Array.from(new Set((Array.isArray(ids)?ids:[]).filter(Boolean).map(String))); renderRoles(); }
  function addById(id){ id=String(id); if(!id||selected.includes(id))return; selected.push(id); renderRoles(); }
  function removeById(id){ id=String(id); selected=selected.filter(x=>x!==id); renderRoles(); }
  function remainingRoles(){ return ROLES.filter(r=>!selected.includes(String(r.id))); }

  function renderRoles(){
    if(!tokensBox||!suggestEl||!hiddenBox) return;

    // Hidden inputs
    hiddenBox.innerHTML='';
    for(const id of selected){
      const i=document.createElement('input'); i.type='hidden'; i.name='role_ids[]'; i.value=id; hiddenBox.appendChild(i);
    }

    // Tokens or placeholder
    const placeholder = tokensBox.getAttribute('data-placeholder') || 'Chọn vai trò';
    if (selected.length === 0) {
      tokensBox.innerHTML = '<span class="ac-placeholder">'+placeholder+'</span>';
    } else {
      const tokens = selected.map(id => {
        const role = ROLES.find(r => String(r.id) === String(id));
        if (!role) return '';
        return '<span class="ac-tag" data-id="'+id+'"><span>'+role.name+'</span><button class="ac-x" type="button" aria-label="Xóa" data-x="'+id+'">×</button></span>';
      }).join('');
      tokensBox.innerHTML = tokens;
    }
    // Không dùng class .is-empty để tránh CSS ẩn hộp
    tokensBox.classList.remove('is-empty');

    // Suggest list
    const list = remainingRoles();
    suggestEl.innerHTML = list.map(r => '<span class="sg" data-add="'+r.id+'"><span class="plus">＋</span><span>'+r.name+'</span></span>').join('');
  }

  if (tokensBox) {
    tokensBox.addEventListener('click', (e)=>{ const id=e.target?.getAttribute?.('data-x'); if(id) removeById(id); });
  }
  if (suggestEl) {
    suggestEl.addEventListener('click', (e)=>{ const chip=e.target.closest?.('[data-add]'); if(chip) addById(chip.getAttribute('data-add')); });
  }

  // ===== Fill modal from Edit button
  function fillFormFromBtn(btn){
    if(!btn||!formEl) return;
    const url=btn.getAttribute('data-update-url')||'';
    const full=btn.getAttribute('data-name')||'';
    const email=btn.getAttribute('data-email')||'';
    const phone=btn.getAttribute('data-phone')||'';
    const address=btn.getAttribute('data-address')||'';
    const status=normStatus(btn.getAttribute('data-status'));
    const avatar=btn.getAttribute('data-avatar')||'';
    const roleIds=(btn.getAttribute('data-role_ids')||'').split(',').map(s=>s.trim()).filter(Boolean);

    formEl.action=url;
    const upd=document.getElementById('__update_action'); if (upd) upd.value=url; // nhớ URL để auto-open sau redirect

    (document.getElementById('ac_name')||{}).value=full;
    (document.getElementById('ac_email')||{}).value=email;
    (document.getElementById('ac_phone')||{}).value=phone;
    (document.getElementById('ac_address')||{}).value=address;

    if (statusSel) { statusSel.value=status; refreshSelect2(); }

    if (imgPrev) { imgPrev.src = avatar || AVATAR_PH; }
    try { if (fileInput) fileInput.value=''; } catch(_) {}

    setSelected(roleIds);
    clearFormErrors();
  }

  // ===== Clear errors helper
  function clearFormErrors(){
    if (!formEl) return;
    formEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    formEl.querySelectorAll('.invalid-feedback').forEach(el => {
      el.classList.remove('d-block'); el.classList.add('d-none'); el.textContent='';
    });
  }

  // ===== Open modal on Edit click
  document.addEventListener('click', function(e){
    const btn = e.target.closest ? e.target.closest('.btnAccountEdit') : null;
    if (!btn) return;
    fillFormFromBtn(btn);
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });

  // ===== Clear errors when user closes modal
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', () => { clearFormErrors(); });
  }

  // ===== Auto open after validation fail
  if (stateEl && stateEl.dataset.hasErrors === '1' && modalEl && formEl && updInput) {
    const updateUrl = updInput.value || '';
    if (updateUrl) {
      const btn = Array.from(document.querySelectorAll('.btnAccountEdit'))
        .find(b => b.getAttribute('data-update-url') === updateUrl);
      if (btn) {
        formEl.action = updateUrl;
        const avatar = btn.getAttribute('data-avatar') || '';
        if (imgPrev && avatar) imgPrev.src = avatar;

        try {
          const arr = JSON.parse((oldRoles && oldRoles.value) ? oldRoles.value : '[]');
          setSelected(Array.isArray(arr) ? arr : []);
        } catch (_) { setSelected([]); }

        refreshSelect2();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    }
  }

  // ===== Tabs URL sync
  function getTabFromURL(){ const u=new URL(location.href); const t=u.searchParams.get('tab'); return ['accounts','roles','stats'].includes(t)?t:'accounts'; }
  function setTabInURL(tab, replace=false){ const u=new URL(location.href); u.searchParams.set('tab',tab); replace?history.replaceState(null,'',u):history.pushState(null,'',u); }
  function showTab(tab){ const trigger=document.querySelector(`[data-bs-target="#${tab}-pane"]`); if(!trigger)return; new bootstrap.Tab(trigger).show(); }
  showTab(getTabFromURL());
  const tabs=document.getElementById('accountTabs');
  if (tabs) {
    tabs.addEventListener('shown.bs.tab', (e) => {
      const pane=e.target.getAttribute('data-bs-target');
      let tab='accounts'; if(pane.includes('roles')) tab='roles'; else if(pane.includes('stats')) tab='stats';
      setTabInURL(tab);
    });
  }
  window.addEventListener('popstate', () => showTab(getTabFromURL()));
  setTabInURL(getTabFromURL(), true);

  // initial render
  renderRoles();
});
