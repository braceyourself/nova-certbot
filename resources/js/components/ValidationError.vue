<template>
    <div v-if="hasError(field)" style="color:darkred">
        <div v-for="e in errors[field]">{{e}}</div>
    </div>
</template>

<script>
    export default {
        name: "ValidationError",
        props: ['errors', 'field'],
        methods: {
            hasError(field) {
                return this.errors[field] !== null
                    && this.errors[field] !== undefined;
            }
        },
    }
</script>

<style scoped>

</style>
