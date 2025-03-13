<template>
    <v-app>
        <Header />
        <v-main>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-card class="elevation-3">
                            <v-toolbar color="primary" dark>
                                <v-toolbar-title>Danh sách đăng ký tham quan</v-toolbar-title>
                                <v-spacer></v-spacer>
                                <Link :href="route('factory-visitors.create')" class="text-decoration-none">
                                <v-btn color="success">
                                    <v-icon left>mdi-plus</v-icon>
                                    Đăng ký mới
                                </v-btn>
                                </Link>
                                <v-btn color="info" @click="syncGoogleSheets" :loading="syncing">
                                    <v-icon left>mdi-sync</v-icon>
                                    Đồng bộ Google Sheets
                                </v-btn>
                            </v-toolbar>

                            <v-card-text>
                                <v-row>
                                    <v-col cols="12" md="4">
                                        <v-text-field :value="search" @input="handleSearchInput"
                                            append-icon="mdi-magnify" label="Tìm kiếm" single-line
                                            hide-details></v-text-field>
                                    </v-col>
                                </v-row>

                                <v-data-table :headers="headers" :items="visitors.data" :loading="loading"
                                    :options.sync="options" :server-items-length="visitors.total" class="elevation-1">
                                    <template v-slot:item.visit_date="{ item }">
                                        {{ formatDate(item.visit_date) }}
                                    </template>

                                    <template v-slot:item.qr_code="{ item }">
                                        <v-btn v-if="item.qr_code" icon small color="primary" @click="showQRCode(item)">
                                            <v-icon>mdi-qrcode</v-icon>
                                        </v-btn>
                                        <span v-else>Chưa có</span>
                                    </template>

                                    <template v-slot:item.actions="{ item }">
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ props }">
                                                <v-btn icon color="primary" @click="showVisitor(item.id)">
                                                    <v-icon>mdi-pencil</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Xem chi tiết</span>
                                        </v-tooltip>

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ props }">
                                                <v-btn icon small color="error" v-bind="props"
                                                    @click="confirmDelete(item)">
                                                    <v-icon>mdi-delete</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Xóa</span>
                                        </v-tooltip>
                                    </template>
                                </v-data-table>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- QR Code Dialog -->
                <v-dialog v-model="qrCodeDialog" max-width="400">
                    <v-card>
                        <v-card-title class="text-h5">
                            Mã QR Code
                        </v-card-title>
                        <v-card-text class="text-center">
                            <v-img v-if="selectedVisitor?.qr_code" :src="`/storage/${selectedVisitor.qr_code}`"
                                max-width="300" class="mx-auto" contain></v-img>
                            <v-btn v-if="selectedVisitor?.qr_code" color="primary" class="mt-4" @click="downloadQRCode">
                                Tải xuống QR Code
                            </v-btn>
                        </v-card-text>
                    </v-card>
                </v-dialog>

                <!-- Delete Confirmation Dialog -->
                <v-dialog v-model="deleteDialog" max-width="400">
                    <v-card>
                        <v-card-title class="text-h5">
                            Xác nhận xóa
                        </v-card-title>
                        <v-card-text>
                            Bạn có chắc chắn muốn xóa đăng ký tham quan này?
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="grey darken-1" text @click="deleteDialog = false">
                                Hủy
                            </v-btn>
                            <v-btn color="error" text @click="deleteVisitor" :loading="deleting">
                                Xóa
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import Header from "../../Components/Header.vue";
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

export default {
    components: {
        Header,
        Link
    },
    props: {
        visitors: Object,
        search: String
    },
    data() {
        return {
            loading: false,
            options: {},
            deleteDialog: false,
            qrCodeDialog: false,
            deleting: false,
            selectedVisitor: null,
            headers: [
                { title: 'Họ và tên', value: 'name' },
                { title: 'Email', value: 'email' },
                { title: 'Số điện thoại', value: 'phone' },
                { title: 'Công ty', value: 'company' },
                { title: 'Ngày tham quan', value: 'visit_date' },
                { title: 'Số lượng người', value: 'number_of_visitors' },
                { title: 'QR Code', value: 'qr_code', sortable: false },
                { title: 'Thao tác', value: 'actions', sortable: false }
            ],
            localSearch: this.search || '',
            syncing: false,
            syncProgress: 0,
            showProgress: false,
            syncMessage: '',
        }
    },
    watch: {
        options: {
            handler() {
                this.loadData();
            },
            deep: true
        }
    },
    methods: {
        formatDate(date) {
            return new Date(date).toLocaleString('vi-VN');
        },
        handleSearchInput: debounce(function (value) {
            this.loadData(value);
        }, 300),
        loadData(searchValue = this.search) {
            this.loading = true;
            const params = {
                page: this.options.page,
                per_page: this.options.itemsPerPage,
                sort_by: this.options.sortBy?.[0],
                sort_desc: this.options.sortDesc?.[0],
                search: searchValue
            };

            router.get('/factory-visitors', params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['visitors']
            })
        },
        showQRCode(visitor) {
            this.selectedVisitor = visitor;
            this.qrCodeDialog = true;
        },
        downloadQRCode() {
            if (!this.selectedVisitor?.qr_code) return;
            const link = document.createElement('a');
            link.href = `/storage/${this.selectedVisitor.qr_code}`;
            link.download = `qr-code-${this.selectedVisitor.name}.svg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        confirmDelete(visitor) {
            this.selectedVisitor = visitor;
            this.deleteDialog = true;
        },
        deleteVisitor() {
            if (!this.selectedVisitor) return;
            this.deleting = true;
            this.$inertia.delete(`/factory-visitors/${this.selectedVisitor.id}`, {
                preserveScroll: true,
                preserveState: true,
                only: ['visitors'],
                onSuccess: () => {
                    this.deleteDialog = false;
                    this.visitors.data = this.visitors.data.filter(v => v.id !== this.selectedVisitor.id);
                    this.visitors.total--;
                },
                onFinish: () => {
                    this.deleting = false;
                }
            });
        },
        showVisitor(id) {
            this.$inertia.visit(`/factory-visitors/${id}`);
        },
        async syncGoogleSheets() {
            this.syncing = true;
            this.showProgress = true;
            this.syncProgress = 0;
            this.syncMessage = 'Đang bắt đầu đồng bộ...';

            try {
                const response = await axios.post(route('google-sheets.sync'), {}, {
                    onUploadProgress: (progressEvent) => {
                        this.syncProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    },
                    onDownloadProgress: (progressEvent) => {
                        this.syncProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    }
                });

                this.syncMessage = response.data.message;
                if (response.data.success_count > 0) {
                    router.reload({ only: ['visitors'] });
                }
            } catch (error) {
                console.error('Sync error:', error);
                this.syncMessage = 'Có lỗi xảy ra khi đồng bộ dữ liệu';
            } finally {
                setTimeout(() => {
                    this.syncing = false;
                    this.showProgress = false;
                    this.syncProgress = 0;
                }, 2000);
            }
        },
    }
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
