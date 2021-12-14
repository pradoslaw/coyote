import axios from 'axios';

const httpClient = axios.create({
  timeout: 30000
});

export default httpClient;
