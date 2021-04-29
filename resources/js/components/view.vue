<template>
    <page>
        <div>
            <div v-if="cert === null">
                {{info_message}}
            </div>

            <certificate v-else :cert="cert" :show-details="true"></certificate>
        </div>
    </page>
</template>

<script>
    import Certificate from "./Certificate";
    import SubPage from "./SubPage";

    export default {
        name: "view",
        data() {
            return {
                info_message: `Loading certificate info for ${this.$route.params.name}`,
                cert: null
            }
        },
        mounted() {
            axios(this.url).then(res => {
                this.cert = res.data;
            }).catch(res => {
                this.info_message = res.response.data[0]
            });
        },
        components: {
            'page': SubPage,
            'certificate': Certificate
        },
        computed: {
            url() {
                return `/nova-vendor/braceyourself/certbot/certificates/${this.$route.params.name}`
            }
        }
    }
</script>

<style scoped>

</style>
