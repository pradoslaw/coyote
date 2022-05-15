import * as Sentry from "@sentry/browser";
import environment from '@/environment';

Sentry.init({
  dsn: environment.sentryDsn,
  release: environment.release,
  ignoreErrors: [
    'Network Error',
    'Non-Error promise rejection captured',
    'Request failed with status code 422',
    'Request aborted',
    'Request failed with status code 401',
    'Request failed with status code 404',
    'Request failed with status code 522',
    'Request failed with status code 500',
    'Request failed with status code 520',
    'Request failed with status code 503',
    'timeout of 0ms exceeded'
  ],
  tracesSampleRate: 0.0,
});
