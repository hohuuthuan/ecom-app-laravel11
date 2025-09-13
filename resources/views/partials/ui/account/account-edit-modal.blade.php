{{-- Modal: Chỉnh sửa tài khoản --}}
<div class="modal fade" id="uiAccountEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="uiAccountEditForm" method="POST" enctype="multipart/form-data" action="{{ old('__update_action') }}">
      @csrf @method('PUT')

      <div class="modal-header">
        <h3 class="modal-title">Chỉnh sửa thông tin</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3 align-items-start">
          {{-- 2 cột: Avatar --}}
          <div class="col-lg-2">
            <div class="ac-avatar">
              <img id="ac_avatar_preview" src="" alt="Avatar">
            </div>

            <label class="form-label mt-2 label-select-image"><b>Chọn hình ảnh</b></label>

            <input id="ac_avatar" name="avatar" type="file" accept="image/*" class="visually-hidden">
            <label for="ac_avatar" class="btn btn-primary w-100 input-select-image" id="btnPickAvatar">
              <i class="fa fa-upload me-1"></i>
            </label>
            <div class="invalid-feedback d-none mt-1" data-err="avatar"></div>
          </div>

          {{-- 10 cột: Thông tin --}}
          <div class="col-lg-10">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="ac_full_name" class="form-label"><b>Họ tên</b> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ac_full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Nhập họ tên" required>
                <div class="invalid-feedback d-none" data-err="full_name"></div>
              </div>

              <div class="col-md-4">
                <label for="ac_email" class="form-label"><b>Email</b> <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="ac_email" name="email" value="{{ old('email') }}" placeholder="Nhập email" required>
                <div class="invalid-feedback d-none" data-err="email"></div>
              </div>

              <div class="col-md-4">
                <label for="ac_phone" class="form-label"><b>Số điện thoại</b> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ac_phone" name="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                <div class="invalid-feedback d-none" data-err="phone"></div>
              </div>

              <div class="col-12">
                <label for="ac_address" class="form-label"><b>Địa chỉ</b></label>
                <textarea id="ac_address" name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ">{{ old('address') }}</textarea>
                <div class="invalid-feedback d-none" data-err="address"></div>
              </div>

              <div class="col-md-6 select2CustomWidth">
                <label for="ac_status" class="form-label"><b>Trạng thái</b></label>
                <select id="ac_status" name="status" class="form-select setupSelect2" data-placeholder="Chọn trạng thái" required>
                  <option value="" disabled {{ old('status') ? '' : 'selected' }}>Chọn trạng thái</option>
                  <option value="ACTIVE" {{ old('status')==='ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                  <option value="BAN"    {{ old('status')==='BAN'    ? 'selected' : '' }}>BAN</option>
                </select>
                <div class="invalid-feedback d-none" data-err="status"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label"><b>Phân quyền</b></label>
                <div id="ac_roles_tokens" class="ac-tags" data-placeholder="Chọn vai trò"></div>
                <div id="ac_roles_suggest" class="ac-suggest mt-2"></div>
                <div id="ac_roles_inputs"></div>
                <div class="invalid-feedback d-none mt-1" data-err="role_ids"></div>
              </div>

              <input type="hidden" name="__update_action" id="__update_action" value="{{ old('__update_action') }}">
              <input type="hidden" id="__old_role_ids" value='@json(old("role_ids", []))'>
            </div>
          </div>
        </div>
      </div>

      @if ($errors->any())
        <script id="accountEditErrors" type="application/json">
          {!! json_encode($errors->toArray(), JSON_UNESCAPED_UNICODE) !!}
        </script>
      @endif

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-cancel-modal" data-bs-dismiss="modal">Huỷ</button>
        <button type="submit" class="btn btn-success btn-submit-modal">Lưu</button>
      </div>
    </form>
  </div>
</div>

<script>
// @ts-nocheck
document.addEventListener('DOMContentLoaded', function () {
  const modalEl   = document.getElementById('uiAccountEditModal');
  const formEl    = document.getElementById('uiAccountEditForm');
  const imgPrev   = document.getElementById('ac_avatar_preview');
  const fileInp   = document.getElementById('ac_avatar');
  const statusSel = document.getElementById('ac_status');

  const rolesBox     = document.getElementById('ac_roles_tokens');
  const rolesSuggest = document.getElementById('ac_roles_suggest');
  const rolesHidden  = document.getElementById('ac_roles_inputs');
  const keepAction   = document.getElementById('__update_action');

  const ROLES = (window.ROLES_MASTER || []).map(r => ({ id:String(r.id), name:r.name }));
  const AVATAR_PH = window.AVATAR_PLACEHOLDER || '';

  // Roles pick-only
  let selected = [];
  function setSelected(ids){ selected=[...new Set((ids||[]).filter(Boolean).map(String))]; renderRoles(); }
  function addRole(id){ id=String(id); if(!id || selected.includes(id)) return; selected.push(id); renderRoles(); }
  function removeRole(id){ selected = selected.filter(x=>x!==String(id)); renderRoles(); }
  function renderRoles(){
    rolesHidden.innerHTML = '';
    selected.forEach(id => {
      const i=document.createElement('input'); i.type='hidden'; i.name='role_ids[]'; i.value=id; rolesHidden.appendChild(i);
    });
    rolesBox.innerHTML = selected.map(id=>{
      const r=ROLES.find(x=>x.id===id); if(!r) return '';
      return `<span class="ac-tag"><span>${r.name}</span><button type="button" class="ac-x" data-x="${id}">×</button></span>`;
    }).join('');
    rolesBox.classList.remove('is-invalid');
    rolesBox.classList.toggle('is-empty', selected.length===0);

    const remain = ROLES.filter(r=>!selected.includes(r.id));
    rolesSuggest.innerHTML = remain.map(r=>`<span class="sg" data-add="${r.id}"><span class="plus">＋</span><span>${r.name}</span></span>`).join('');
  }
  rolesBox.addEventListener('click', e=>{ const id=e.target.getAttribute('data-x'); if(id) removeRole(id); });
  rolesSuggest.addEventListener('click', e=>{ const n=e.target.closest('[data-add]'); if(n) addRole(n.getAttribute('data-add')); });

  // Avatar preview
  fileInp?.addEventListener('change', () => {
    const f = fileInp.files?.[0]; if(!f) return;
    imgPrev.src = URL.createObjectURL(f);
  });

  // Fill form from list button
  function normStatus(s){ s=String(s||'').toUpperCase().trim(); return s==='BAN'?'BAN':'ACTIVE'; }
  function fillFormFromBtn(btn){
    const url = btn.getAttribute('data-update-url') || formEl.action;
    formEl.action = url;
    if (keepAction) keepAction.value = url;

    formEl.querySelector('#ac_full_name').value = btn.getAttribute('data-full_name') || '';
    formEl.querySelector('#ac_email').value     = btn.getAttribute('data-email') || '';
    formEl.querySelector('#ac_phone').value     = btn.getAttribute('data-phone') || '';
    formEl.querySelector('#ac_address').value   = btn.getAttribute('data-address') || '';

    imgPrev.src = btn.getAttribute('data-avatar') || AVATAR_PH;
    if (fileInp) try{ fileInp.value=''; } catch(_) {}

    const st = normStatus(btn.getAttribute('data-status'));
    statusSel.value = st;
    if (window.jQuery && $.fn?.select2) $('#ac_status').trigger('change.select2');

    const roleIds = (btn.getAttribute('data-role_ids')||'').split(',').map(s=>s.trim()).filter(Boolean);
    setSelected(roleIds);
  }

  // Errors
  function applyErrorsBag(errors){
    const bag = {};
    Object.entries(errors || {}).forEach(([k, arr]) => {
      const key = k.split('.')[0];
      if (!bag[key] && Array.isArray(arr) && arr[0]) bag[key] = arr[0];
    });
    function showErr(name, msg){
      const target = formEl.querySelector(`[name="${name}"]`);
      const slot   = formEl.querySelector(`[data-err="${name}"]`);
      if (name === 'role_ids') {
        rolesBox.classList.add('is-invalid');
      } else if (name === 'status' && window.jQuery && $.fn?.select2) {
        $('#ac_status').siblings('.select2-container').find('.select2-selection').addClass('select2-invalid');
      }
      if (target && name !== 'role_ids') target.classList.add('is-invalid');
      if (slot) { slot.textContent = msg; slot.classList.remove('d-none'); slot.classList.add('d-block'); }
    }
    Object.entries(bag).forEach(([n,m]) => showErr(n,m));
  }
  function clearErrors(){
    formEl.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));
    rolesBox.classList.remove('is-invalid');
    formEl.querySelectorAll('[data-err]').forEach(el=>{ el.textContent=''; el.classList.remove('d-block'); el.classList.add('d-none'); });
    if (window.jQuery && $.fn?.select2) $('#ac_status').siblings('.select2-container').find('.select2-selection').removeClass('select2-invalid');
  }

  // Open from list
  document.addEventListener('click', e=>{
    const btn = e.target.closest?.('.btnAccountEdit');
    if (!btn) return;
    clearErrors();
    fillFormFromBtn(btn);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });

  // Auto-open if server returned errors + giữ avatar gốc + đúng action
  const errScript = document.getElementById('accountEditErrors');
  if (errScript) {
    try {
      const bag = JSON.parse(errScript.textContent || '{}');

      const oldAction = document.getElementById('__update_action')?.value || '';
      if (oldAction) {
        const btnMatch = document.querySelector(`.btnAccountEdit[data-update-url="${oldAction}"]`);
        const src = btnMatch ? (btnMatch.getAttribute('data-avatar') || AVATAR_PH) : (AVATAR_PH || '');
        if (imgPrev) imgPrev.src = src;
        if (fileInp) try{ fileInp.value=''; } catch(_) {}
        formEl.action = oldAction;
      }
      if (imgPrev && !imgPrev.src) imgPrev.src = AVATAR_PH;

      const raw = document.getElementById('__old_role_ids')?.value || '[]';
      const oldRoleIds = JSON.parse(raw);
      setSelected(Array.isArray(oldRoleIds) ? oldRoleIds : []);

      const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
      inst.show();

      if (window.jQuery && $.fn?.select2) $('#ac_status').trigger('change.select2');

      applyErrorsBag(bag);
    } catch(_) {}
  }

  // Close → clear and remove error node
  modalEl.addEventListener('hidden.bs.modal', function(){
    clearErrors();
    document.getElementById('accountEditErrors')?.remove();
  });

  // init
  if (imgPrev && !imgPrev.src) imgPrev.src = AVATAR_PH;
  renderRoles();
});
</script>
