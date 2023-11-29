import axios from './bootstrap';

// Create an Axios instance, you can set additional defaults here
const instance = axios.create({
  baseURL: '/api',
  // other default configurations
});

// Optionally add request and response interceptors
instance.interceptors.request.use(
  config => {
    // Do something before request is sent (e.g., set Auth headers)
    return config;
  },
  error => {
    // Do something with request error
    return Promise.reject(error);
  }
);

export default instance;
