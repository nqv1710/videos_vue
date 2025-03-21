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
        <v-btn text @click="goToFactory">
            <v-icon>mdi-factory</v-icon> Factory
        </v-btn>
        <v-btn text @click="goToEmail">
            <v-icon>mdi-email</v-icon> Email
        </v-btn>
        <!-- 🔹 Nút mở QR Scanner -->
        <v-btn color="primary" @click="isScanning = true">
            <v-icon left>mdi-qrcode-scan</v-icon> Scan QR
        </v-btn>
        <!-- 🔹 Dialog chứa QR Scanner -->
        <v-dialog v-model="isScanning" max-width="500">
            <v-card>
                <v-card-title>Quét mã QR</v-card-title>
                <v-card-text>
                    <QrScanner @scanned="handleScanResult" />
                </v-card-text>
                <v-card-actions>
                    <v-btn color="red" @click="isScanning = false">Đóng</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app-bar>
</template>

<script>
import QrScanner from "./QrScanner.vue";
export default {
    components: { QrScanner },
    data() {
        return {
            appName: import.meta.env.VITE_APP_NAME || 'My App',
            isScanning: false,
        };
    },
    methods: {
        goToHome() {
            this.$inertia.visit('/');
        },
        goToUsers() {
            this.$inertia.visit('/users');
        },
        goToFactory() {
            this.$inertia.visit('/factory-visitors');
        },
        goToEmail() {
            this.$inertia.visit('/gmail/inbox');
        },
        handleScanResult(result) {
            console.log('Kết quả quét QR:', result);
            this.isScanning = false;
        },
    },
};
</script>

<style scoped>
.v-toolbar-title {
    font-weight: bold;
}
</style>