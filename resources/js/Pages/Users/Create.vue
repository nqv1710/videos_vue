<template>
    <v-app>
        <Head title="Create User" />
        <Header />
        <v-main>
            <v-container>
                <v-row justify="center">
                    <v-col cols="12" md="8">
                        <v-card class="elevation-3">
                            <v-toolbar color="primary" dark>
                                <v-btn icon @click="goBack">
                                    <v-icon>mdi-arrow-left</v-icon>
                                </v-btn>
                                <v-toolbar-title>Create User</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text>
                                <v-form @submit.prevent="createUser">
                                    <v-text-field v-model="form.name" label="Name" required></v-text-field>
                                    <v-text-field v-model="form.email" label="Email" type="email"
                                        required></v-text-field>
                                    <v-text-field v-model="form.password" label="Password" type="password"
                                        required></v-text-field>
                                    <div class="d-flex justify-end mt-4">
                                        <v-btn color="primary" type="submit">Create</v-btn>
                                    </div>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import Header from "../../Components/Header.vue";
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

export default {
    components: { Header,Head },
    setup() {
        const form = useForm({
            name: '',
            email: '',
            password: '',
        });

        function createUser() {
            form.post('/users');
        }

        function goBack() {
            window.history.back();
        }

        return { form, createUser, goBack };
    },
};
</script>

<style scoped>
.v-card-title {
    font-size: 1.5rem;
    font-weight: bold;
}
</style>
