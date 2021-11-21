import * as Sentry from "@sentry/browser";
import { Integrations } from "@sentry/tracing";
import environment from '@/environment';

Sentry.init({
  dsn: environment.sentryDsn,
  release: environment.release,
  ignoreErrors: ['Network Error', 'Non-Error promise rejection captured'],
  integrations: [new Integrations.BrowserTracing()],
  tracesSampleRate: 0.0,
});
