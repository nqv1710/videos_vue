<template>
    <v-app>
        <Head :title="email.subject" />
        <Header />

        <v-main>
            <v-container>
                <v-btn color="primary" @click="$inertia.visit('/gmail/inbox')">
                    <v-icon left>mdi-arrow-left</v-icon> Quay lại
                </v-btn>

                <v-card>
                    <v-card-title class="headline">{{ email.subject }}</v-card-title>
                    <v-card-subtitle>
                        <strong>Từ:</strong> {{ email.from }} <br>
                        <strong>Ngày:</strong> {{ email.date }}
                    </v-card-subtitle>
                    <v-card-text v-html="email.body"></v-card-text>

                    <!-- Hiển thị danh sách ảnh -->
                    <!-- <v-row v-if="email.images.length" class="mt-4">
                        <v-col v-for="(image, index) in email.images" :key="index" cols="12" sm="6" md="4">
                            <v-img :src="image" alt="Email Image" contain class="email-image"></v-img>
                        </v-col>
                    </v-row> -->

                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import Header from "../../Components/Header.vue";
import { Head } from "@inertiajs/vue3";

export default {
    components: { Header, Head },
    props: {
        email: {
            type: Object,
            required: true
        }
    }
};
</script>

<style scoped>
.email-image {
    border-radius: 8px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
    width: 100%;
    height: auto;
}
</style>
