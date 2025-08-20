<!-- CommentList.vue -->
<template>
    <div>
        <h1>Quản lý lượt xem và bình luận</h1>
        <div class="group-btn">
            <div><b>TỔNG: </b>{{ comments.length }}</div>
            <button @click="exportExcel">Xuất Excel</button>
        </div>

        <el-table :data="comments" style="width: 100%">
            <el-table-column type="index" label="STT" width="50" />
            <el-table-column prop="username" label="Người tạo" />
            <el-table-column prop="xml_id" label="Mã nhân viên" />
            <el-table-column prop="departments" label="Phòng ban" />
            <el-table-column prop="type" label="Type" />
            <el-table-column prop="message" label="Comment" />
            <el-table-column prop="created_at" label="Ngày tạo" />
        </el-table>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx'
import useAuth from '../../composables/useAuth'
import { usePage } from '@inertiajs/vue3'
const { user, token, ensureUserLoaded } = useAuth();
const comments = ref([])
// Get props passed from route `/admin/list-comment/{id}/{user_id}`
const { props } = usePage()
const videoId = computed(() => props.id)

const fetchComments = async () => {
    try {
        const res = await axios.get(`/api/detail_comments`, { params: { id: videoId.value, user_id: props.user_id } });
        comments.value = res.data
    } catch (err) {
        console.error('Error loading comments:', err)
    }
}

const exportExcel = () => {
    const table = document.querySelector('.el-table__body-wrapper table')
    const wb = XLSX.utils.table_to_book(table, { sheet: "Comment_view" })
    XLSX.writeFile(wb, 'comment_view.xlsx')
}

onMounted(async () => {
    await ensureUserLoaded()
    await fetchComments()
})
</script>

<style scoped>
.group-btn {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
}

button {
    background: #409eff;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 4px;
    cursor: pointer;
}
</style>