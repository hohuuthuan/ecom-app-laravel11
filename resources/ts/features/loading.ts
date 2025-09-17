let holder: HTMLElement | null = null;

function ensure(): HTMLElement {
  if (holder) return holder;
  holder = document.createElement('div');
  holder.className = 'fixed inset-0 z-[1100] hidden';
  holder.innerHTML = `
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
      <div class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow">
        <span class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-primary-600"></span>
        <span class="text-sm" id="__loading_msg">Đang tải…</span>
      </div>
    </div>`;
  document.body.appendChild(holder);
  return holder;
}

export function show(msg?: string) {
  const el = ensure();
  const m = el.querySelector('#__loading_msg') as HTMLElement | null;
  if (m && msg) m.textContent = msg;
  el.classList.remove('hidden');
}

export function hide() {
  ensure().classList.add('hidden');
}
