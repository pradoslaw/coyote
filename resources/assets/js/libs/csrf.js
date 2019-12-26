import axios from "axios";

export default function setToken(token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

  // deprecated
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': token
    }
  });
}
