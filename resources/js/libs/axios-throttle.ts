import axios, { AxiosRequestConfig } from 'axios';

const pending: string[] = [];

function signature(config: AxiosRequestConfig): string {
  return config.method + (config.url as string);
}

function removePending(config: AxiosRequestConfig): void {
  const url = signature(config);

  if (pending.includes(url)) {
    delete pending[pending.findIndex(item => item === url)];
  }
}

axios.interceptors.request.use((config: AxiosRequestConfig) => {
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
    if (err.config) {
      removePending(err.config);
    }

    return Promise.reject(err);
  }
);
