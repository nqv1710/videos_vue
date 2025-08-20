import { createStore } from 'vuex'
import Cookies from 'js-cookie'
import axios from 'axios'

const initialToken = Cookies.get('token') || null;
if (initialToken) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${initialToken}`;
}

const auth = {
    namespaced: true,
    state: () => ({
        user: null,
        token: initialToken,
        authenticated: false,
    }),
    getters: {
        user: state => state.user,
        id: state => state.user?.id || null,
        token: state => state.token,
        authenticated: state => state.authenticated,
    },
    mutations: {
        SET_USER(state, user) {
            state.user = user;
            state.authenticated = !!user;
        },
        SET_TOKEN(state, token) {
            state.token = token;
            Cookies.set('token', token, { path: '/', expires: 7 });
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },
        LOGOUT(state) {
            state.user = null;
            state.token = null;
            state.authenticated = false;
            Cookies.remove('token');
            delete axios.defaults.headers.common['Authorization'];
        },
    },
    actions: {
        async setAuth({ commit }, { token, user }) {
            commit('SET_TOKEN', token);
            commit('SET_USER', user);
        },
        async getUser({ commit }) {
            try {
                const res = await axios.get('/api/user');
                commit('SET_USER', res.data);
                return res.data;
            } catch (error) {
                commit('LOGOUT');
                throw error;
            }
        },
        async logout({ commit }) {
            commit('LOGOUT');
        }
    }
};

const store = createStore({
    modules: {
        auth
    }
});

export default store;