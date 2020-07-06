import Vue from 'vue';
import Router from 'vue-router';
import AllStats from '../views/AllStats';
import PostStats from '../views/PostStats';
import PostList from '../views/PostList';
import EditPost from '../views/EditPost';
import EditTag from '../views/EditTag';
import TagList from '../views/TagList';
import EditTopic from '../views/EditTopic';
import TopicList from '../views/TopicList';
import EditSettings from '../views/EditSettings';
import EditUser from '../views/EditUser';
import UserList from '../views/UserList';
import store from '../store';

Vue.use(Router);

export default [
    {
        path: '/',
        redirect: '/stats',
    },
    {
        path: '/stats',
        name: 'all-stats',
        component: AllStats,
    },
    {
        path: '/stats/:id',
        name: 'post-stats',
        component: PostStats,
    },
    {
        path: '/posts',
        name: 'posts',
        component: PostList,
    },
    {
        path: '/posts/create',
        name: 'create-post',
        component: EditPost,
    },
    {
        path: '/posts/:id/edit',
        name: 'edit-post',
        component: EditPost,
    },
    {
        path: '/tags',
        name: 'tags',
        component: TagList,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/tags/create',
        name: 'create-tag',
        component: EditTag,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/tags/:id/edit',
        name: 'edit-tag',
        component: EditTag,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/topics',
        name: 'topics',
        component: TopicList,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/topics/create',
        name: 'create-topic',
        component: EditTopic,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/topics/:id/edit',
        name: 'edit-topic',
        component: EditTopic,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/settings',
        name: 'edit-settings',
        component: EditSettings,
    },
    {
        path: '/users',
        name: 'users',
        component: UserList,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '/users/:id/edit',
        name: 'edit-user',
        component: EditUser,
        beforeEnter: (to, from, next) => {
            if (store.state.user.admin === 1 || store.state.user.id === to.params.id) {
                next();
            } else {
                next({ name: 'all-stats' });
            }
        },
    },
    {
        path: '*',
        name: 'catch-all',
        redirect: '/stats',
    },
];
