import axios from "axios";
import { SOCKET_ID } from './realtime.ts';

export default function setToken(token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
  axios.defaults.headers.common['X-Socket-ID'] = SOCKET_ID;

  if (window.$ !== undefined) {
    // deprecated
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': token
      }
    });
  }

}
