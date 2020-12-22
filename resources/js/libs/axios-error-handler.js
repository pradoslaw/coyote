import axios, { Cancel } from "axios";

export default function errorHandler(cb) {
  axios.interceptors.response.use(null, err => {
    let message = '';

    if (err instanceof Cancel || (err.config.hasOwnProperty("errorHandle") && err.config.errorHandle === false)) {
      return Promise.reject(err);
    }

    if (err.response) {
      if (err.response.data.errors) {
        const errors = err.response.data.errors;

        message = errors[Object.keys(errors)[0]][0];
      }
      else if (err.response.data.message) {
        message = err.response.data.message;
      }
      else {
        message = err.response.statusText;
      }
    }
    else {
      message = err.message;
    }

    cb(message);

    return Promise.reject(err);
  });
}
