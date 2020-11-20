import axios, { AxiosRequestConfig } from 'axios';

const pending: string[] = [];

function signature(config: AxiosRequestConfig): string {
  return config.method + (config.url as string) + Object.entries(config.params ?? []).join(',');
}

function removePending(config: AxiosRequestConfig): void {
  const url = signature(config);
  const index = pending.indexOf(url);

  if (index > -1) {
    pending.splice(index, 1);
  }
}

axios.interceptors.request.use((config: AxiosRequestConfig) => {
  // don't mess with request with cancelToken already set.
  if ('cancelToken' in config) {
    return config;
  }

  const url = signature(config);

  if (pending.includes(url)) {
    throw new axios.Cancel('Throttle detected.');
  }
  pending.push(url);

  return config;
}, err => Promise.reject(err));

axios.interceptors.response.use(
  response => {
    removePending(response.config);

    return response;
  },
  err => {
    // system worked. request was canceled. unfortunately we err.config is undefined so we don't know
    // which url cause this. it's better to clear an array
    if (err instanceof axios.Cancel) {
      pending.splice(0, pending.length);
    }

    if (err.config) {
      removePending(err.config);
    }

    return Promise.reject(err);
  }
);
