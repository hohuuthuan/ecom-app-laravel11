(function(){
  const fileInput = document.getElementById('productImageFile');
  const previewBox = document.getElementById('previewBox');
  const btnClear = document.getElementById('btnClearImage');

  function setPreview(file){
    const reader = new FileReader();
    reader.onload = e=>{
      previewBox.innerHTML = '<img alt="preview">';
      previewBox.querySelector('img').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  fileInput?.addEventListener('change', e=>{
    const f = e.target.files?.[0];
    if(f){ setPreview(f); }
  });

  btnClear?.addEventListener('click', ()=>{
    if(fileInput){ fileInput.value = ''; }
    previewBox.innerHTML = '<span class="text-muted"><i class="fa-regular fa-image me-1"></i> 300×300</span>';
  });
})();

(function(){
  function readDataset(el){
    if(!el){ return []; }
    const raw = el.getAttribute('data-source');
    if(!raw){ return []; }
    try{
      const arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr : [];
    }catch(_e){
      return [];
    }
  }

  function toTagifyList(items){
    return items.map(x => ({
      value: String(x.id),
      name: x.name
    }));
  }

  function setupTagify(inputEl, data){
    if(!inputEl){ return null; }
    const whitelist = toTagifyList(data);

    const tagify = new Tagify(inputEl, {
      enforceWhitelist: true,
      whitelist,
      tagTextProp: 'name',
      dropdown: {
        enabled: 0,
        maxItems: 50,
        closeOnSelect: false,
        highlightFirst: true,
        mapValueTo: 'name',
        searchKeys: ['name']
      },
      // submit lên server dạng "1,2,3"
      originalInputValueFormat: arr => arr.map(v => v.value).join(','),
      editTags: false
    });

    function openDropdown(){ tagify.dropdown.show.call(tagify, inputEl.value); }
    inputEl.addEventListener('focus', openDropdown);
    inputEl.addEventListener('click', openDropdown);

    tagify.on('add', e=>{
      const tag = e.detail.tag;
      tag.title = e.detail.data?.name || e.detail.data?.value || '';
    });

    return tagify;
  }

  const catEl = document.getElementById('categoriesInput');
  const autEl = document.getElementById('authorsInput');

  setupTagify(catEl, readDataset(catEl));
  setupTagify(autEl, readDataset(autEl));
})();


(function(){
  const btnSaveDraftBottom = document.getElementById('btnSaveDraftBottom');
  const form = document.getElementById('productCreateForm');

  function onSaveDraft(){
    alert('Lưu nháp: demo giao diện');
  }

  btnSaveDraftBottom?.addEventListener('click', onSaveDraft);

  form?.addEventListener('submit', function(e){
    e.preventDefault();
    alert('Submit: demo giao diện');
  });
})();
