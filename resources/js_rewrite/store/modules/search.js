import request from '../../mixins/request';

const initialState = {
    index: [],
};

const state = { ...initialState };

const actions = {
    buildIndex(context, stale = false) {
        if (stale) {
            context.commit('RESET_STATE');
        }

        request.methods
            .request()
            .get('/api/search/posts')
            .then(({ data }) => {
                context.commit('UPDATE_INDEX', data);
            });

        if (context.rootGetters['settings/isAdmin']) {
            request.methods
                .request()
                .get('/api/search/tags')
                .then(({ data }) => {
                    context.commit('UPDATE_INDEX', data);
                });
            request.methods
                .request()
                .get('/api/search/topics')
                .then(({ data }) => {
                    context.commit('UPDATE_INDEX', data);
                });
            request.methods
                .request()
                .get('/api/search/users')
                .then(({ data }) => {
                    context.commit('UPDATE_INDEX', data);
                });
        }
    },
};

const mutations = {
    UPDATE_INDEX(state, data) {
        state.index.push(...data);
    },

    RESET_STATE(state) {
        state.index = [];
    },
};

const getters = {
    //
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
};