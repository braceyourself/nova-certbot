<template>
    <div>
        <router-link to="/NovaCertbot/new" class="btn btn-primary">Create New Cert</router-link>
        <button v-if="!cache_cleared" class="btn btn-outline-danger" @click="clearCertbotCache">Clear Cache</button>
        <button v-if="!cache_updated" class="btn btn-outline-success" @click="updateCertbotCache">Update Cache</button>

        <div v-if="loading_data || data === []">
            Loading Certificate data...
        </div>
        <div v-else v-for="item in data" class="flex  my-3">
            <certificate :cert="item"></certificate>
        </div>

    </div>
</template>

<script>
    import Certificate from "./Certificate";

    export default {
        data() {
            return {
                cache_cleared:false,
                cache_updated:false,
                loading_data: false,
                url: '/nova-vendor/braceyourself/certbot/certificates?sort=expires_in',
                data: [],
            }
        },
        components: {
            certificate: Certificate
        },
        methods: {
            clearCertbotCache() {
                axios.delete('/nova-vendor/braceyourself/certbot/certificate-cache/certificates ').then(res => {
                    this.cache_cleared = true
                })
            },
            updateCertbotCache() {
                axios.post('/nova-vendor/braceyourself/certbot/certificate-cache').then(res => {
                    this.cache_updated = true
                    this.loadData();
                })
            },
            loadData() {
                this.loading_data = true;
                axios(this.url).then(r => {
                    this.data = r.data.data;
                }).finally(res => {
                    this.loading_data = false
                })
            }

        },
        mounted() {
            this.loadData();
        },

    }
</script>

<style scoped lang="scss">
    .btn {
        padding: 5px;
    }
</style>
