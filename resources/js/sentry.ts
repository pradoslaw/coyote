import * as Sentry from "@sentry/browser";
import environment from '@/environment';

Sentry.init({
  dsn: environment.sentryDsn,
  release: environment.release,
  ignoreErrors: ['Network Error', 'Non-Error promise rejection captured', 'Request failed with status code 422'],
  tracesSampleRate: 0.0,
});
