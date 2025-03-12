<template>
    <v-app>
        <Head title="User Management" />
        <!-- Header -->
        <Header />

        <v-main>
            <v-container>
                <v-row justify="center">
                    <v-col cols="12" md="10">
                        <v-card class="elevation-3">
                            <!-- Title & Add Button -->
                            <v-toolbar color="primary" dark>
                                <v-toolbar-title>User Management</v-toolbar-title>
                                <v-spacer></v-spacer>
                                <v-btn color="white" @click="createUser">
                                    <v-icon left>mdi-plus</v-icon> Add User
                                </v-btn>
                            </v-toolbar>

                            <!-- User Table -->
                            <v-data-table :items="users" :headers="headers" class="elevation-1" item-key="id">
                                <template v-slot:item.actions="{ item }">
                                    <v-btn icon color="primary" @click="editUser(item.id)">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon color="error" @click="deleteUser(item.id)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </template>
                            </v-data-table>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import Header from "../../Components/Header.vue";
import { Head } from '@inertiajs/vue3';

export default {
    components: { Header, Head },
    props: { users: Array },
    data() {
        return {
            headers: [
                { title: "Name", value: "name", sortable: true },
                { title: "Email", value: "email", sortable: true },
                { title: "Actions", value: "actions", sortable: false },
            ],
        };
    },
    methods: {
        createUser() {
            this.$inertia.visit(`/users/create`);
        },
        editUser(id) {
            this.$inertia.visit(`/users/${id}/edit`);
        },
        deleteUser(id) {
            if (confirm("Are you sure you want to delete this user?")) {
                this.$inertia.delete(`/users/${id}`);
            }
        },
    },
};
</script>

<style scoped>
.v-toolbar-title {
    font-size: 1.2rem;
    font-weight: bold;
}
</style>
