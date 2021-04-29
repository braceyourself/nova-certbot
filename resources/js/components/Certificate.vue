<template>
    <div class="card" style="width: 100%">
        <div class="card-header">
            <h1>{{cert.certificate_name}}</h1>
        </div>
        <div class="card-body">
            <h3>Domains:</h3>
            <ul>
                <li v-for="d in cert.domains">{{d}}</li>
            </ul>
            <div v-if="showDetails">
                <h3>Details:</h3>
                <div>expires_on: {{cert.expires_on}} ({{cert.expires_in}} days from now)</div>
                <div>serial_number: {{cert.serial_number}}</div>
                <div>certificate_path: {{cert.certificate_path}}</div>
                <div>private_key_path: {{cert.private_key_path}}</div>
            </div>
            <div v-if="action_response !== null">
                <hr>
                <h4>Command Output:</h4>
                <pre v-for="line in action_response">{{line}}</pre>
            </div>
        </div>
        <div class="card-footer flex justify-content-between">
            <div>
                <router-link v-if="!viewing" :to="view()" class="btn btn-primary">View</router-link>
                <div v-else-if="processing">
                    Running command...
                </div>
                <div v-else class="flex flex-nowrap">
                    <button class="btn btn-success mx-1" @click="renew()">Renew</button>
                    <button class="btn btn-danger mx-1" @click="deleteCert()">Delete</button>
                    <select v-model="action_type" class="d-inline mx-1">
                        <option v-for="(action, i) in actions" :value="i">{{action}}</option>
                    </select>
                </div>
            </div>
            <span>Expires in {{cert.expires_in}} days</span>
        </div>
    </div>

</template>

<script>

    export default {
        name: "Certificate",
        data() {
            return {
                processing: false,
                action_type: 'test',
                actions: {
                    'prod': 'production',
                    'test': 'dry-run'
                },
                action_response: null
            }
        },
        props: {
            cert: {},
            showDetails: {
                default: false
            }
        },
        computed: {
            viewing() {
                return this.$route.path.includes(this.cert.certificate_name)
            },
            url() {
                return this.cert.api_path + '?' + this.query;
            },
            query() {
                let dry_run = this.action_type === 'test' ? 1 : 0;

                return `dry_run=${dry_run}`
            }

        },
        methods: {
            view() {
                return this.cert.frontend_path;
            },
            deleteCert() {
                this.action_response = null;
                this.processing = true;
                axios.delete(this.url)
                    .then(this.handlSuccessResponse)
                    .catch(this.handleErrorResponse)
                    .finally(this.afterResponse)
            },
            renew() {
                this.action_response = null;
                this.processing = true;
                axios.patch(this.url)
                    .then(this.handlSuccessResponse)
                    .catch(this.handleErrorResponse)
                    .finally(this.afterResponse)
            },
            revoke() {
                this.action_response = null;
                this.processing = true;
                axios.delete(this.url + '&revoke=1')
                    .then(this.handlSuccessResponse)
                    .catch(this.handleErrorResponse)
                    .finally(this.afterResponse)
            },

            handlSuccessResponse(res) {
                this.action_response = res.data.output;
            },
            handleErrorResponse(res) {
                this.action_response = res.response.data;
            },
            afterResponse(res) {
                this.processing = false;
            }
        }
    }
</script>

<style scoped>

</style>
