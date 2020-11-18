import axios, { AxiosRequestConfig } from 'axios';

const pending: string[] = [];

function getUrl(config: AxiosRequestConfig): string {
  return config.url as string;
}

function removePending(config: AxiosRequestConfig): void {
  const url = getUrl(config);

  if (pending.includes(url)) {
    delete pending[pending.findIndex(item => item === url)];
  }
}

axios.interceptors.request.use((config: AxiosRequestConfig) => {
  if (config.method === 'get') {
    return config;
  }

  const url = getUrl(config);

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
