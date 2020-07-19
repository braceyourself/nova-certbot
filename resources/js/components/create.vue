<template>
    <page>
        <h1>{{title}}</h1>
        <div class="card">

            <div v-if="output !== null" class="card-body">
                <div v-for="line in output">{{line}}</div>
            </div>

            <div v-else class="card-body">
                <div class="form-group">
                    <button class="btn btn-outline-primary" @click="addDomain">
                        <i class="material-icons">add</i>
                        Add Domain
                    </button>
                </div>
                <div class="form-group">
                    <div v-for="(domain, i) in cert.domains">
                        <div class="flex flex-row">
                            <input type="text" class="ml-2 d-inline" v-model="cert.domains[i]">
                            <button class="btn btn-outline-danger ml-2" style="max-height: 38px"
                                    @click="removeDomain(i)">
                                <i class="material-icons d-inline">remove</i>
                            </button>
                        </div>
                        <validation-error :errors="errors" :field="getErrorKey('domains', i)"></validation-error>
                    </div>
                    <validation-error :errors="errors" field="domains"/>
                </div>

                <div class="form-group">
                    <label>
                        <span class="d-inline">Dry Run</span>
                        <input type="checkbox" v-model="cert.dry_run" class="d-inline">
                    </label>
                </div>

            </div>
            <div class="card-footer">
                <div v-if="creating">
                    Requesting new certificate...
                </div>
                <div v-else-if="created">
                    Certificate Created!
                    <router-link :to="cert_link" class="btn btn-primary">View</router-link>
                    <button class="btn btn-success" @click="resetCert">Create Another</button>
                </div>
                <div v-else class="flex justify-content-between">
                    <button @click="createCert" class="btn btn-success">Create</button>
                    <span class="align-self-end small">{{command}}</span>
                </div>
            </div>
        </div>

    </page>

</template>

<script>
    import SubPage from "./SubPage";
    import ValidationError from "./ValidationError";

    export default {
        name: "create",
        data() {
            return {
                output: null,
                creating: false,
                created: false,
                cert: null,
                errors: []
            }
        },
        mounted() {
            this.resetCert();
        },
        components: {
            'page': SubPage,
            'validation-error': ValidationError
        },
        computed: {
            title() {
                if (this.created) {
                    return 'Success!'
                }

                return 'Create a new Certificate';
            },
            command() {

                let command = `certbot certonly`;
                command += this.cert.dry_run ? ' --dry-run' : '';
                command += ' -d ' + this.cert.domains.join(' ');

                return command;
            },
            cert_link() {
                return `NovaCertbot/${this.cert.name}`
            }
        },
        methods: {
            getErrorKey(key, index) {
                return `${key}.${index}`;
            },
            resetCert() {
                this.output = null;
                this.creating = false;
                this.created = false;
                this.cert = {
                    domains: [],
                    dry_run: true
                };
            },
            addDomain() {
                this.cert.domains.push('')
            },
            removeDomain(index) {
                this.cert.domains.splice(index, 1)
            },
            createCert() {
                this.errors = [];
                this.creating = true;

                axios.post('/nova-vendor/braceyourself/certbot/certificates', this.cert).then(res => {
                    this.output = res.data;
                    this.created = true;
                })
                    .catch(res => {
                        console.log(res.response.data.errors);
                        this.errors = res.response.data.errors;
                    })
                    .finally(res => {
                        this.creating = false;
                    });
            },
        }
    }
</script>


<style lang="scss">

</style>
