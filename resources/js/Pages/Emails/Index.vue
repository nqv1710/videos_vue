<template>
    <v-app>
        <Head title="Inbox" />
        <Header />
        <v-main>
            <v-container>
                <v-row justify="center">
                    <v-col cols="12" md="10">
                        <v-card class="elevation-3">
                            <v-toolbar color="primary" dark>
                                <v-toolbar-title>Email Inbox</v-toolbar-title>
                                <v-spacer></v-spacer>
                            </v-toolbar>

                            <v-data-table :items="emails" :headers="headers" class="elevation-1" item-key="id">
                                <template v-slot:item.actions="{ item }">
                                    <v-btn icon color="primary" @click="viewEmail(item.id)">
                                        <v-icon>mdi-eye</v-icon>
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
import { Head } from "@inertiajs/vue3";

export default {
    components: { Header, Head },
    props: { emails: Array },
    data() {
        return {
            headers: [
                { title: "From", value: "from", sortable: true },
                { title: "Subject", value: "subject", sortable: true },
                { title: "Date", value: "date", sortable: true },
                { title: "Actions", value: "actions", sortable: false },
            ],
        };
    },
    methods: {
        viewEmail(id) {
            this.$inertia.visit(`/emails/${id}`);
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
