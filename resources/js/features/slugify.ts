export function slugify(input: string): string {
  if (!input) {
    return '';
  }
  let s = input.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  s = s.replace(/đ/gi, 'd');
  s = s.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
  return s;
}

/** Liên kết 2 input: name -> slug (tự sinh khi slug trống hoặc đang sync) */
export function attachNameToSlug(nameEl: HTMLInputElement, slugEl: HTMLInputElement): () => void {
  let manual = false;

  const onSlugInput = () => {
    manual = slugEl.value.trim().length > 0;
  };
  const onNameInput = () => {
    if (!manual) {
      slugEl.value = slugify(nameEl.value);
    }
  };

  slugEl.addEventListener('input', onSlugInput);
  nameEl.addEventListener('input', onNameInput);

  return () => {
    slugEl.removeEventListener('input', onSlugInput);
    nameEl.removeEventListener('input', onNameInput);
  };
}
