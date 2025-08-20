import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ---- Global auth handling (works on all pages) ----
const setAuthHeaderFromLocalStorage = () => {
  try {
    const t = localStorage.getItem('api_token');
    if (t) {
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${t}`;
    } else if (window.axios?.defaults?.headers?.common?.Authorization) {
      delete window.axios.defaults.headers.common['Authorization'];
    }
  } catch (_) {}
};

setAuthHeaderFromLocalStorage();

// Sync token changes across tabs
window.addEventListener('storage', (e) => {
  if (e.key === 'api_token') setAuthHeaderFromLocalStorage();
});

// Intercept 401 to detect expired/invalid token
let isRedirectingFor401 = false;
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    if (status === 401) {
      try { localStorage.removeItem('api_token'); } catch (_) {}
      setAuthHeaderFromLocalStorage();
      if (!isRedirectingFor401 && window.location.pathname !== '/bitrix/login') {
        isRedirectingFor401 = true;
        const current = window.location.pathname + window.location.search + window.location.hash;
        const url = `/bitrix/login?redirect=${encodeURIComponent(current)}`;
        window.location.assign(url);
      }
    }
    return Promise.reject(error);
  }
);
