import 'jquery';
declare global {
  interface JQuery {
    select2: (...args: any[]) => JQuery;
  }
}
export {};
