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
  const url = getUrl(config);

  if (pending.includes(url)) {
    throw new axios.Cancel('Operation canceled by the user.');
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
    removePending(err.config);

    return Promise.reject(err);
  }
);
