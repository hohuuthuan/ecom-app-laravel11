import { mountPasswordToggle } from '../features/password-toggle';

export default async function init(root: HTMLElement): Promise<void> {
  mountPasswordToggle(document);
}
