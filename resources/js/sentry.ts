import * as Sentry from "@sentry/browser";
import environment from '@/environment';

Sentry.init({
  dsn: environment.sentryDsn,
  release: environment.release,
  ignoreErrors: ['Network Error', 'Non-Error promise rejection captured'],
  tracesSampleRate: 0.0,
});
