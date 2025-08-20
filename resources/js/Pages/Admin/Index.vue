<template>

    <Head title="Videos" />
    <el-dialog v-model="showModal" title="Thông tin video" width="650px" @close="closeModal">
        <el-form :model="formDataSubmit" :label-position="'left'" :rules="rules" ref="ruleForm"
            style="padding: 20px 10px;" label-width="150px">
            <el-form-item label="Tiêu đề:" prop="title">
                <el-input v-model="formDataSubmit.title"></el-input>
            </el-form-item>
            <el-form-item label="Description video:" prop="description">
                <!-- <el-input type="textarea" :rows="2" v-model="formDataSubmit.description"></el-input> -->
                <div ref="editorRef" style="width: 100%; height: 150px;"></div>
            </el-form-item>
            <el-form-item label="Hash tag:" prop="hashtag">
                <el-input v-model="formDataSubmit.hashtag" placeholder="báo chí, sự kiện"></el-input>
            </el-form-item>
            <el-form-item label="Topic video:" prop="topic">
                <el-cascader v-model="formDataSubmit.topic" :options="optionTopic"
                    :props="{ multiple: true, checkStrictly: true }" style="width: 100%" clearable></el-cascader>
            </el-form-item>
            <el-form-item label="Loại video:" prop="typeVideo">
                <el-select v-model="formDataSubmit.typeVideo" placeholder="Chọn loại video">
                    <el-option label="Youtube" value="Youtube"></el-option>
                    <el-option label="Tiktok" value="Tiktok"></el-option>
                    <el-option label="Server" value="Server"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Link video:" prop="url">
                <el-input v-model="formDataSubmit.url"></el-input>
            </el-form-item>
            <el-form-item label="Link share:">
                <el-input v-model="formDataSubmit.linkShare"></el-input>
            </el-form-item>

            <div style="display: flex; justify-content: center;">
                <el-button type="primary" @click="handleSubmit">Lưu</el-button>
                <el-button @click="closeModal">Hủy</el-button>
            </div>
        </el-form>
    </el-dialog>

    <div class="admin-container">
        <div class="btn-control">
            <span>TỔNG: {{ listDataVideo.length }}</span>
            <el-button type="primary" @click="showModal = true" v-if="!showModal">Thêm video</el-button>
        </div>

        <el-table :data="listDataVideo" border style="width: 100%">
            <el-table-column v-for="col in columns" :key="col.prop" :prop="col.prop" :label="col.label"
                :width="col.width">
                <template #default="scope">
                    <template v-if="scope && scope.row">
                        <!-- Cột có customRender -->
                        <div v-if="col.customRender" v-html="col.customRender(scope.row)"></div>

                        <!-- cột tiêu đề -->
                        <template v-else-if="col.prop === 'title'">
                            <div style="display: flex; gap: 10px">
                                <a
                                    :href="`https://bitrix.esuhai.org/page-custom/module-video/list-video/?id=${scope.row.id}`">{{
                                        scope.row.title }}</a>
                                <el-tooltip class="item" effect="dark" content="Copy link" placement="top">
                                    <i style="margin-top: 5px; color: #f57e09; font-size: 15px; cursor: pointer;"
                                        class="fa-regular fa-copy" @click="copyLink(scope.row.id)"></i>
                                </el-tooltip>
                            </div>
                        </template>

                        <!-- cột description -->
                        <template v-else-if="col.prop === 'description'">
                            <div v-html="decodeHTML(scope.row.description)"></div>
                        </template>


                        <!-- cột topic -->
                        <template v-else-if="col.prop === 'topic'">
                            <!-- Chuyển từ JSON thành đối tượng và hiển thị topic -->
                            <span>{{ formatTopic(scope.row.topic) }}</span>
                        </template>

                        <!-- Cột hành động -->
                        <template v-else-if="col.prop === 'action'">
                            <div style="text-align: center;">
                                <el-button type="warning" @click="openEdit(scope.row)">Sửa</el-button>
                            </div>
                        </template>
                        <!-- Cột quản lý comment' -->
                        <template v-else-if="col.prop === 'comment'">
                            <div style="text-align: center;">
                                <el-button type="primary" @click="openComment(scope.row?.id)">Chi tiết</el-button>
                            </div>
                        </template>

                        <!-- Các cột thông thường -->
                        <template v-else>
                            {{ scope.row[col.prop] }}
                        </template>
                    </template>
                </template>
            </el-table-column>
        </el-table>
    </div>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import axios from 'axios'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'
import useAuth from '../../composables/useAuth'
import { ElNotification, ElMessage } from 'element-plus'

const { user, token, ensureUserLoaded } = useAuth();

// Data
const quill = ref(null);
const editorRef = ref(null);

const listDataVideo = ref([]);
const flagEdit = ref(false);
const formDataSubmit = ref({
    title: '',
    description: '',
    hashtag: '',
    topic: '',
    typeVideo: '',
    url: '',
    linkShare: '',
    id: ''
});
const optionTopic = ref([{
    value: 'Coaching MS',
    label: 'Coaching MS',
    children: [{
        value: 'Chương trình tham gia',
        label: 'Chương trình tham gia'
    },
    {
        value: 'Chi phí tham gia',
        label: 'Chi phí tham gia'
    },
    {
        value: 'Điều kiện tham gia',
        label: 'Điều kiện tham gia'
    },
    {
        value: 'Lương & Chế độ phúc lợi',
        label: 'Lương & Chế độ phúc lợi'
    },
    {
        value: 'Định hướng tương lai',
        label: 'Định hướng tương lai'
    },
    {
        value: 'Định hướng nghề nghiệp',
        label: 'Định hướng nghề nghiệp'
    },
    {
        value: 'Đào tạo Tiếng Nhật',
        label: 'Đào tạo Tiếng Nhật'
    },
    {
        value: 'Hỗ trợ từ Esuhai',
        label: 'Hỗ trợ từ Esuhai'
    }
    ]
},
{
    value: 'Cẩm nang Tư vấn',
    label: 'Cẩm nang Tư vấn',
    children: [{
        value: 'Chương trình tham gia',
        label: 'Chương trình tham gia'
    },
    {
        value: 'Chi phí tham gia',
        label: 'Chi phí tham gia'
    },
    {
        value: 'Điều kiện tham gia',
        label: 'Điều kiện tham gia'
    },
    {
        value: 'Ngành nghề làm việc',
        label: 'Ngành nghề làm việc'
    },
    {
        value: 'Lương & Chế độ phúc lợi',
        label: 'Lương & Chế độ phúc lợi'
    },
    {
        value: 'Đào tạo từ Esuhai',
        label: 'Đào tạo từ Esuhai'
    },
    {
        value: 'Hỗ trợ từ Esuhai',
        label: 'Hỗ trợ từ Esuhai'
    },
    {
        value: 'Định hướng tương lai',
        label: 'Định hướng tương lai'
    }
    ]
},
{
    value: 'Chia sẻ Nhân viên',
    label: 'Chia sẻ Nhân viên'
},
{
    value: 'Thầy Sơn - CareerCoach',
    label: 'Thầy Sơn - CareerCoach'
},
{
    value: 'Tôi vẽ tương lai',
    label: 'Tôi vẽ tương lai'
}
]);
const showModal = ref(false);
const rules = ref({
    title: [{
        required: true,
        message: 'Vui lòng nhập tiêu đề',
        trigger: 'blur'
    }],
    description: [{
        required: true,
        message: 'Vui lòng nhập description',
        trigger: 'blur'
    }],
    hashtag: [{
        required: true,
        message: 'Vui lòng nhập hashtag',
        trigger: 'blur'
    }],
    topic: [{
        required: true,
        message: 'Vui lòng chọn topic',
        trigger: 'change'
    }],
    typeVideo: [{
        required: true,
        message: 'Vui lòng chọn loại video',
        trigger: 'change'
    }],
    url: [{
        required: true,
        message: 'Vui lòng nhập URL',
        trigger: 'blur'
    }],
});
const columns = ref([{
    prop: 'id',
    label: 'ID',
    width: '50'
},
{
    prop: 'title',
    label: 'Tiêu đề',
    width: '200'
},
{
    prop: 'description',
    label: 'Description',
    width: '400'
},
{
    prop: 'hashtag',
    label: 'Hashtag',
    width: '200'
},
{
    prop: 'topic',
    label: 'Topic'
},
{
    prop: 'created_at',
    label: 'Ngày tạo',
    width: '160'
},
{
    prop: 'action',
    label: 'Chỉnh sửa',
    width: '120'
},
{
    prop: 'comment',
    label: 'Quản lý comment',
    width: '120'
},
]);
const ruleForm = ref(null);
const openComment = (id) => {
    if (id) {
        window.open(
            '/admin/list-comment/' +
            id + '/' + user.value?.bitrix_user_id);
    }
}

// Chuẩn hóa dữ liệu topic (có thể là chuỗi JSON, mảng hoặc object)
const toTopicArray = (topic) => {
    if (!topic) return [];
    if (Array.isArray(topic)) return topic;
    if (typeof topic === 'string') {
        try {
            const parsed = JSON.parse(topic);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }
    if (typeof topic === 'object') {
        // Nếu là object (đã parse) thì trả thẳng ra mảng nếu là mảng, hoặc lấy values
        return Array.isArray(topic) ? topic : Object.values(topic);
    }
    return [];
};

const formatTopic = (topic) => {
    const parsedTopics = toTopicArray(topic);
    if (!parsedTopics.length) return 'Không có dữ liệu';
    return parsedTopics.map(t => t.parent ? `${t.parent} -> ${t.name}` : t.name).join(', ');
};

const initializeQuill = () => {
    return new Promise((resolve) => {
        if (!quill.value) {
            quill.value = new Quill(editorRef.value, { // Sử dụng editorRef
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{
                            'header': 1
                        }, {
                            'header': 2
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'script': 'sub'
                        }, {
                            'script': 'super'
                        }],
                        [{
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        [{
                            'direction': 'rtl'
                        }],
                        [{
                            'size': ['small', false, 'large',
                                'huge'
                            ]
                        }],
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'font': []
                        }],
                        [{
                            'align': []
                        }],
                        ['clean']
                    ]
                }
            });
            quill.value.on('text-change', () => {
                formDataSubmit.value.description = quill.value.root.innerHTML;
            });
        }
        resolve();
    });
};

const openEdit = async (data) => {
    showModal.value = true;

    flagEdit.value = true;
    const topicsArr = toTopicArray(data.topic);
    const parsedTopics = topicsArr.map(t => t.parent ? [t.parent, t.name] : [t.name]);
    formDataSubmit.value = {
        ...data,
        topic: parsedTopics, // Gán dữ liệu topics vào form
    };
    // Chờ modal được render hoàn toàn trước khi gán nội dung cho Quill
    await nextTick();

    // Khởi tạo Quill và chờ hoàn thành
    await initializeQuill();

    // Bây giờ bạn có thể gán giá trị
    if (quill.value) {
        quill.value.root.innerHTML = decodeHTML(data.description) || '';
    } else {
        console.error('Quill chưa được khởi tạo!');
    }

};

const closeModal = () => {
    formDataSubmit.value = {
        title: '',
        description: '',
        hashtag: '',
        topic: '',
        typeVideo: '',
        url: '',
        linkShare: ''
    };
    if (ruleForm.value) {
        ruleForm.value.resetFields();
    }
    if (quill.value) {
        quill.value.root.innerHTML = ''; // Reset nội dung
    }
    showModal.value = false;
};

const copyLink = (id) => {
    const linkCopy =
        'https://bitrix.esuhai.org/page-custom/module-video/list-video?id=' +
        id;
    navigator.clipboard.writeText(linkCopy).then(() => {
        ElMessage({
            message: 'Đã copy!',
            type: 'success',
        })
    });
}

const handleSubmit = async (e) => {
    e.preventDefault();
    ruleForm.value.validate(async (valid) => {
        if (!valid) return;

        // Chuyển đổi topic thành định dạng phù hợp
        const formattedTopics = formDataSubmit.value.topic.map(item => {
            // Nếu item là một mảng, lấy tên topic cha và con
            if (Array.isArray(item)) {
                return {
                    name: item[item.length -
                        1], // Tên topic là phần tử cuối cùng
                    parent: item.length > 1 ? item[0] : null // Topic cha là phần tử đầu tiên nếu có
                };
            } else {
                // Nếu không phải là mảng, item là topic không có cha
                return {
                    name: item,
                    parent: null
                };
            }
        });

        const payload = {
            ...formDataSubmit.value,
            // Backend requires createdBy as nullable|string
            createdBy: user.value?.bitrix_user_id != null ? String(user.value.bitrix_user_id) : null,
            action: flagEdit.value ? 'edit' : 'create',
            id: flagEdit.value ? formDataSubmit.value.id : null,
            topics: formattedTopics // Gửi danh sách topic đã định dạng
        };

        try {
            // Quick client-side validation to match backend rules
            const url = String(payload.url || '')
            if (!/^https?:\/\//i.test(url)) {
                ElNotification({
                    title: 'URL không hợp lệ',
                    message: 'Vui lòng nhập URL đầy đủ, bao gồm http hoặc https',
                    duration: 4000,
                    type: 'error'
                })
                return
            }
            // Send JSON and explicitly include Bearer token from composable
            const response = await axios.post('api/video/action', payload, {
                headers: token.value ? { Authorization: `Bearer ${token.value}` } : {}
            });
            if (response.data.status === 'success') {
                ElNotification({
                    title: 'Thành công',
                    message: flagEdit.value ?
                        'Bạn vừa chỉnh sửa thành công!' : 'Bạn vừa tạo thành công!',
                    duration: 4000,
                    type: 'success'
                });
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                ElNotification({
                    title: 'Thất bại',
                    message: 'Đã xảy ra lỗi xin vui lòng thử lại',
                    duration: 4000,
                    type: 'error'
                });
            }
        } catch (error) {
            console.error(error);
            // Show validation errors if present
            const errors = error?.response?.status === 422 ? error.response.data.errors : null
            const firstMsg = errors ? Object.values(errors).flat()[0] : null
            ElNotification({
                title: 'Thất bại',
                message: firstMsg || 'Đã xảy ra lỗi xin vui lòng thử lại',
                duration: 4000,
                type: 'error'
            });
        }
    });
};

// "giải mã" chuỗi HTML escaped này trước khi chèn vào v-html.
const decodeHTML = (html) => {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

onMounted(async () => {
    await ensureUserLoaded();

    console.log(user.value);

    watch(showModal, async (newValue) => {
        if (newValue) {
            await nextTick();
            if (!quill.value && editorRef.value) {
                quill.value = new Quill(editorRef.value, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{
                                'header': 1
                            }, {
                                'header': 2
                            }],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'script': 'sub'
                            }, {
                                'script': 'super'
                            }],
                            [{
                                'indent': '-1'
                            }, {
                                'indent': '+1'
                            }],
                            [{
                                'direction': 'rtl'
                            }],
                            [{
                                'size': ['small', false, 'large',
                                    'huge'
                                ]
                            }],
                            [{
                                'header': [1, 2, 3, 4, 5, 6, false]
                            }],
                            [{
                                'color': []
                            }, {
                                'background': []
                            }],
                            [{
                                'font': []
                            }],
                            [{
                                'align': []
                            }],
                            ['clean']
                        ]
                    }
                });

                // Bắt sự kiện text-change để lưu nội dung vào formDataSubmit
                quill.value.on('text-change', () => {
                    formDataSubmit.value.description = quill.value.root
                        .innerHTML;
                });
            }
        } else {
            // Nếu modal đóng, ẩn trình soạn thảo (tuỳ chọn nếu cần)
            if (quill.value) {
                quill.value.root.innerHTML = ''; // Reset lại nội dung nếu cần
            }
        }
    });
    fetchVideos();
});

const fetchVideos = async () => {
    try {
        const response = await axios.get(`api/videos-list`, {
            params: {
                ...listDataVideo,
            }
        });
        listDataVideo.value = response.data.videos;
        await nextTick();
    } catch (error) {
        console.error("An error occurred: ", error);
    }
}
</script>