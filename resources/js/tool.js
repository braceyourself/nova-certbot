import Vue from 'vue'
import Buefy from 'buefy'

Vue.use(Buefy)

Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'NovaCertbot',
            path: '/NovaCertbot',
            component: require('./components/index'),
        },
        {
            name: 'NovaCertbot',
            path: '/NovaCertbot/new',
            component: require('./components/create'),
        },
        {
            name: 'NovaCertbot',
            path: '/NovaCertbot/certificates/:name',
            component: require('./components/view'),
        },
    ])
})
