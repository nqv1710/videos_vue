<template>
    <v-app>
        <Header />
        <v-main>
            <v-container>
                <v-row justify="center">
                    <v-col cols="12" md="8">
                        <v-card class="elevation-3">
                            <v-toolbar color="primary" dark>
                                <v-toolbar-title>Đăng ký tham quan nhà máy</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text>
                                <v-alert
                                    v-if="Object.keys(form.errors).length > 0"
                                    type="error"
                                    class="mb-4"
                                >
                                    <div class="text-subtitle-1 font-weight-bold mb-2">
                                        Vui lòng kiểm tra lại các thông tin sau:
                                    </div>
                                    <ul>
                                        <li v-for="(error, field) in form.errors" :key="field">
                                            {{ error }}
                                        </li>
                                    </ul>
                                </v-alert>

                                <v-form @submit.prevent="submitForm">
                                    <v-text-field
                                        v-model="form.name"
                                        label="Họ và tên"
                                        required
                                        :error-messages="form.errors.name"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="form.email"
                                        label="Email"
                                        type="email"
                                        required
                                        :error-messages="form.errors.email"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="form.phone"
                                        label="Số điện thoại"
                                        required
                                        :error-messages="form.errors.phone"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="form.company"
                                        label="Công ty"
                                        :error-messages="form.errors.company"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="form.visit_date"
                                        label="Ngày tham quan"
                                        type="datetime-local"
                                        required
                                        :error-messages="form.errors.visit_date"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="form.number_of_visitors"
                                        label="Số lượng người tham quan"
                                        type="number"
                                        min="1"
                                        required
                                        :error-messages="form.errors.number_of_visitors"
                                    ></v-text-field>

                                    <v-textarea
                                        v-model="form.purpose"
                                        label="Mục đích tham quan"
                                        :error-messages="form.errors.purpose"
                                    ></v-textarea>

                                    <div class="d-flex justify-end mt-4">
                                        <v-btn
                                            color="primary"
                                            type="submit"
                                            :loading="form.processing"
                                        >
                                            Đăng ký
                                        </v-btn>
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

export default {
    components: { Header },
    setup() {
        const form = useForm({
            name: '',
            email: '',
            phone: '',
            company: '',
            visit_date: '',
            number_of_visitors: 1,
            purpose: '',
        });

        function submitForm() {
            console.log('Form data:', form);
            form.post('/factory-visitors');
        }

        return { form, submitForm };
    },
};
</script>
