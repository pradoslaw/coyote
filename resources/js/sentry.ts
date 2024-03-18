import * as Sentry from "@sentry/browser";
import environment from '@/environment';

Sentry.init({
  dsn: environment.sentryDsn,
  release: environment.release,
  ignoreErrors: [
    'Network Error',
    'Non-Error promise rejection captured',
    'Request aborted',
    'Request failed with status code 401',
    'Request failed with status code 403',
    'Request failed with status code 404',
    'Request failed with status code 422',
    'Request failed with status code 469',
    'Request failed with status code 500',
    'Request failed with status code 502',
    'Request failed with status code 503',
    'Request failed with status code 520',
    'Request failed with status code 522',
    'timeout of 0ms exceeded',
    "null is not an object (evaluating 'ReaderArticleFinderJS.adoptableArticle().outerHTML')",
    "null is not an object (evaluating 'o.itemSize=3')",
    'Improper range',
    "undefined is not an object (evaluating 'hj.settings.site_id')",
  ],
  tracesSampleRate: 0.0,
});
