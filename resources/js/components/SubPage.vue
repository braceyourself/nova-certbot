<template>
    <div>
        <router-link :to="last_page" class="btn btn-primary p-2">
            <i class="material-icons" style="position: relative; top:2px">
                keyboard_backspace
            </i>
        </router-link>
        <hr>
        <slot></slot>

    </div>
</template>

<script>
    export default {
        name: "SubPage",
        props: {
            path: {
                default: '/NovaCertbot'
            }
        },
        computed: {
            last_page() {
                return this.path;
            }
        }
    }
</script>

<style scoped>

</style>
