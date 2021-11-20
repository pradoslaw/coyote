interface Environment {
  sentryDsn?: string;
  vapidKey?: string;
  release?: string;
}

const environment: Environment = {
  sentryDsn: process.env.FRONTEND_SENTRY_DSN,
  vapidKey: process.env.VAPID_PUBLIC,
  release: process.env.RELEASE
}

export default environment;
