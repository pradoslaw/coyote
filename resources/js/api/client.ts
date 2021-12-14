import axios from 'axios';

const httpClient = axios.create({
  timeout: 1000
});

export default httpClient;
