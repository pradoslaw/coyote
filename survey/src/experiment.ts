import diffFormat from "./time/diffFormat";

const title = 'Układ treści w komentarzach.';

const reason = 'Hierarchia informacji w obecnym układzie utrudnia szybkie zweryfikowanie kto jest autorem komentarza ' +
  'oraz kiedy komentarz został napisany.';

const solution = 'Proponujemy zmianę, która zakłada uporządkowanie treści według następującej hierarchii: ' +
  '<code>kto?</code>, <code>kiedy?</code>, <code>co?</code>. ' +
  'Dzięki temu szybko uzyskamy informację o autorze komentarza, dacie jego napisania oraz jego treści.';

export const experiment = {
  title,
  reason,
  solution,
  dueTime: diffFormat(secondsUntil('2024-08-20 14:00:00')),
  imageLegacy: '/img/survey/postCommentStyle/legacy.png',
  imageModern: '/img/survey/postCommentStyle/modern.png',
};

function secondsUntil(dateFormat: string): number {
  return millisecondsDifference(dateFormat) / 1000;
}

function millisecondsDifference(dateFormat: string): number {
  return new Date(dateFormat).getTime() - new Date().getTime();
}
