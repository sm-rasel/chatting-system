import { createStore } from 'vuex'
import axios from "axios";

export default createStore({
  state: {
    user: null,
    token: null
  },
  mutations: {
    setUser(state, user) {
      state.user = user
    },
    setToken(state, token) {
      state.token = token
    },
    clearAuth(state) {
      state.user = null;
      state.token =  null;
    }
  },
  actions: {
    async register({ commit }, credentials) {
      const res = await axios.post(`${import.meta.env.VITE_API_URL}/api/register`, credentials)
      commit('setUser', res.data.user);
      commit('setToken', res.data.token);
      localStorage.setItem('token', res.data.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`
    },
    async login({ commit }, credentials) {
      const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/login`, credentials);
      commit('setUser', response.data.user);
      commit('setToken', response.data.token);
      localStorage.setItem('token', response.data.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
    },

    async logout({ commit }) {
      await axios.post(`${import.meta.env.VITE_API_URL}/api/logout`);
      commit('clearAuth');
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    },
    initializeAuth({ commit }) {
      const token = localStorage.getItem('token');
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      axios.get(`${import.meta.env.VITE_API_URL}/api/user`).then(res => {
        commit('setUser', res.data);
        commit('setToken', token)
      }).catch(() => {
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      })
    }
  }
});
