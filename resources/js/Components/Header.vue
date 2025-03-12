<template>
    <v-app-bar app>
        <v-toolbar-title>{{ appName }}</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn text @click="goToHome">
            <v-icon>mdi-home</v-icon> Home
        </v-btn>
        <v-btn text @click="goToUsers">
            <v-icon>mdi-account</v-icon> Users
        </v-btn>
    </v-app-bar>
</template>

<script>
export default {
    data() {
        return {
            appName: import.meta.env.VITE_APP_NAME || 'My App',
        };
    },
    methods: {
        goToHome() {
            this.$inertia.visit('/');
        },
        goToUsers() {
            this.$inertia.visit('/users');
        },
    },
};
</script>

<style scoped>
.v-toolbar-title {
    font-weight: bold;
}
</style>