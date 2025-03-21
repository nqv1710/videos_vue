<template>
    <v-app>
        <Header />
        <v-main>
            <v-container>
                <v-row justify="center">
                    <v-col cols="12" md="8">
                        <v-card class="elevation-3">
                            <v-toolbar color="primary" dark>
                                <v-toolbar-title>Chi tiết đăng ký tham quan</v-toolbar-title>
                                <v-spacer></v-spacer>
                                <Link :href="route('factory-visitors.index')" class="text-decoration-none">
                                <v-btn color="white" variant="outlined">
                                    <v-icon left>mdi-arrow-left</v-icon>
                                    Quay lại
                                </v-btn>
                                </Link>
                            </v-toolbar>

                            <v-card-text>
                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-list>
                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-account</v-icon>
                                                </template>
                                                <v-list-item-title>Họ và tên</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.name }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-email</v-icon>
                                                </template>
                                                <v-list-item-title>Email</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.email }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-phone</v-icon>
                                                </template>
                                                <v-list-item-title>Số điện thoại</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.phone }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-office-building</v-icon>
                                                </template>
                                                <v-list-item-title>Công ty</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.company || 'Không có'
                                                    }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-calendar</v-icon>
                                                </template>
                                                <v-list-item-title>Ngày tham quan</v-list-item-title>
                                                <v-list-item-subtitle>{{ formatDate(visitor.visit_date)
                                                    }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-account-group</v-icon>
                                                </template>
                                                <v-list-item-title>Số lượng người</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.number_of_visitors
                                                    }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-text</v-icon>
                                                </template>
                                                <v-list-item-title>Mục đích tham quan</v-list-item-title>
                                                <v-list-item-subtitle>{{ visitor.purpose || 'Không có'
                                                    }}</v-list-item-subtitle>
                                            </v-list-item>

                                            <v-list-item>
                                                <template v-slot:prepend>
                                                    <v-icon color="primary">mdi-information</v-icon>
                                                </template>
                                                <v-list-item-title>Trạng thái</v-list-item-title>
                                                <v-list-item-subtitle>
                                                    <v-chip :color="getStatusColor(visitor.status)" class="ml-2">
                                                        {{ getStatusText(visitor.status) }}
                                                    </v-chip>
                                                </v-list-item-subtitle>
                                            </v-list-item>
                                        </v-list>
                                    </v-col>

                                    <v-col cols="12" md="6" class="text-center">
                                        <v-card class="mb-4">
                                            <v-card-title>Cập nhật trạng thái</v-card-title>
                                            <v-select v-model="selectedStatus" :items="statusOptions"
                                                label="Chọn trạng thái" item-title="text" item-value="value" />

                                        </v-card>

                                        <template v-if="visitor.qr_code">
                                            <h3 class="text-h6 mb-4">Mã QR Code</h3>
                                            <v-img :src="`/storage/${visitor.qr_code}`" max-width="300"
                                                class="mx-auto mb-4" contain></v-img>
                                            <v-btn color="primary" @click="downloadQRCode">
                                                <v-icon left>mdi-download</v-icon>
                                                Tải xuống QR Code
                                            </v-btn>
                                        </template>
                                        <v-alert v-else type="info" text="QR Code chưa được tạo"></v-alert>
                                    </v-col>
                                </v-row>
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
import { Link } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

export default {
    components: {
        Header,
        Link
    },
    props: {
        visitor: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            selectedStatus: this.visitor.status,
            statusOptions: [
                { value: 'pending', text: 'Chờ duyệt' },
                { value: 'approved', text: 'Đã duyệt' },
                { value: 'rejected', text: 'Từ chối' },
                { value: 'completed', text: 'Hoàn thành' }
            ]
        }
    },
    methods: {
        formatDate(date) {
            return new Date(date).toLocaleString('vi-VN');
        },
        downloadQRCode() {
            if (!this.visitor?.qr_code) return;
            const link = document.createElement('a');
            link.href = `/storage/${this.visitor.qr_code}`;
            link.download = `qr-code-${this.visitor.name}.svg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        getStatusColor(status) {
            const colors = {
                pending: 'warning',
                approved: 'success',
                rejected: 'error',
                completed: 'info'
            };
            return colors[status] || 'grey';
        },
        getStatusText(status) {
            const texts = {
                pending: 'Chờ duyệt',
                approved: 'Đã duyệt',
                rejected: 'Từ chối',
                completed: 'Hoàn thành'
            };
            return texts[status] || 'Không xác định';
        },
        async updateStatus() {
            try {
                await this.$inertia.patch(route('factory-visitors.update-status', this.visitor.id), {
                    status: this.selectedStatus
                }, {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Hiển thị thông báo thành công
                        toast("Trạng thái đã được cập nhật", {
                            "theme": "auto",
                            "type": "default",
                            "dangerouslyHTMLString": true
                        })

                    },
                    onError: (errors) => {
                        // Hiển thị thông báo lỗi
                        toast("Không thể cập nhật trạng thái. Vui lòng thử lại.", {
                            "theme": "auto",
                            "type": "error",
                            "dangerouslyHTMLString": true
                        })
                    }
                });
            } catch (error) {
                toast("Không thể cập nhật trạng thái. Vui lòng thử lại.", {
                    "theme": "auto",
                    "type": "error",
                    "dangerouslyHTMLString": true
                })
            }
        }
    }, watch: {
        selectedStatus(newValue) {
            this.updateStatus();
        }
    }
}
</script>
