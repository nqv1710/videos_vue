<template>
    <div class="qr-scanner">
        <qrcode-stream @decode="onDecode" @init="onInit"></qrcode-stream>

        <!-- Nút chọn ảnh QR -->
        <input type="file" accept="image/*" @change="onFileSelected" />

        <qrcode-drop-zone @decode="onDecode">
            <p>Kéo thả ảnh QR vào đây hoặc chọn file</p>
        </qrcode-drop-zone>
    </div>
</template>

<script>
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from "vue-qrcode-reader";

export default {
    components: { QrcodeStream, QrcodeDropZone, QrcodeCapture },
    data() {
        return {
            QrScanner: null,
        };
    },
    async mounted() {
        try {
            // Import QrScanner khi component được mount
            const QrScannerModule = await import('qr-scanner');
            this.QrScanner = QrScannerModule.default || QrScannerModule;
        } catch (error) {
            console.error("Không thể tải thư viện QR Scanner:", error);
        }
    },
    methods: {
        onDecode(result) {
            this.$emit("scanned", result);
        },
        async onInit(promise) {
            try {
                await promise;
            } catch (error) {
                console.error("Lỗi camera:", error);
            }
        },
        async onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!this.QrScanner) {
                try {
                    // Import QrScanner nếu chưa được tải
                    const QrScannerModule = await import('qr-scanner');
                    this.QrScanner = QrScannerModule.default || QrScannerModule;
                } catch (error) {
                    console.error("Không thể tải thư viện QR Scanner:", error);
                    return;
                }
            }

            try {
                const result = await this.QrScanner.scanImage(file);
                this.onDecode(result);
            } catch (error) {
                console.error("Không thể đọc QR từ ảnh:", error);
            }
        },
    },
};
</script>

<style>
.qr-scanner {
    text-align: center;
}
</style>