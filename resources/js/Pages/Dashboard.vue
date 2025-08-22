<template>

    <Head title="Videos" />
    <div class="loading" id="loading" v-if="loading">
        <div style="display: flex; align-items: center; justify-content: center; height: inherit;">
            <div class="loader"></div>
        </div>
    </div>
    <div class="list-topic">
        <!-- Hiển thị topic cha -->
        <div v-for="(topic, index) in listTopic" :key="index" class="wrap-topic">
            <div @click="filterTopic(topic.value)" class="topic-parent">
                {{ topic.label }}
            </div>

            <!-- Nếu có children, hiển thị chúng -->
            <div v-if="topic.children && topic.children.length" class="list-sub-topic">
                <div v-for="(subTopic, subIndex) in topic.children" :key="subIndex" @click="filterTopic(subTopic.value)"
                    class="topic-child">
                    {{ subTopic.label }}
                </div>
            </div>
        </div>
    </div>

    <!-- search -->
    <form class="form-search" @submit.prevent="handleSearch">
        <input type="text" v-model="paramVideo.searchInput" placeholder="Tìm kiếm...">
        <i class="fa-solid fa-magnifying-glass" @click="handleSearch"></i>
    </form>

    <div class="wrap-video" v-for="video in videos" :key="video.id">
        <div class="content-left">
            <h2>{{ video.title }}</h2>
            <p class="description-video" v-if="video.description" v-html="decodeHTML(video.description)"></p>
            <div class="wrap-hashtag">
                <time class="created-video"><i class="fa-solid fa-clock"></i>{{ video.created_at }}</time>
                <div v-if="video.hashtag" class="list-hashtag">
                    <span v-for="(item, index) in video.hashtag.split(',')" :key="index"
                        @click="filterHash(item.trim())">
                        <template v-if="index > 0">,</template>
                        #{{ item.trim() }}
                    </span>
                </div>
            </div>
            <div style="position: relative;">
                <div class="detail-video">
                    <div class="wrap-content-video">
                        <!-- video youtube -->
                        <div v-if="video.typeVideo == 'Youtube'">
                            <iframe width="100%" style="height: calc(100vh - 200px); width: 100%;"
                                :src="`${video.url}${video?.isPlaying ? '&autoplay=1' : ''}`" frameborder="0"
                                allow="autoplay; encrypted-media" allowfullscreen></iframe>
                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; cursor: pointer;"
                                v-if="!video?.isPlaying" @click="playVideoIframe(video)">
                            </div>
                        </div>

                        <!-- video tiktok -->

                        <div v-if="video.typeVideo == 'Tiktok'">
                            <iframe width="100%" style="height: calc(100vh - 200px); width: 100%;"
                                :src="`https://www.tiktok.com/player/v1/${video.url}${video?.isPlaying ? '?autoplay=1' : ''}`"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; gyroscope; picture-in-picture;"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; cursor: pointer;"
                                v-if="!video?.isPlaying" @click="playVideoIframe(video)">
                            </div>
                        </div>


                        <!-- video server -->
                        <video width="100%" style="height: calc(100vh - 200px)" controls
                            v-if="video.typeVideo == 'Server'" @play="handlePlay(video, $event)">
                            <source :src="`${video.url}`" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="comment-video" v-if="video.flagComment">
                        <div class="wrap-comment">
                            <button @click="closeComment(video)" class="btn-close-comment"><i
                                    class="fa-solid fa-xmark"></i></button>
                            <h4>Bình luận:
                                <span>{{ video.comments?.length > 0 ? video.comments?.length : 0 }}</span>
                            </h4>
                            <div class="list-comment" v-if="video.comments?.length > 0">
                                <div class="comment" v-for="(comment, index) in video.comments" :key="index">
                                    <div class="infor">
                                        <div class="username">{{ comment.username }}</div>
                                        <div class="created_at">{{ formatDate(comment.created_at) }}</div>
                                    </div>
                                    <div class="message">{{ comment.message }}</div>
                                </div>
                            </div>
                            <p v-else>No comments found.</p>
                            <form @submit.prevent="handleSubmit(video, 'comment')" id="commentForm">
                                <textarea v-model="video.myComment" placeholder="Your comment" rows="1"
                                    required></textarea><br>
                                <button type="submit">SEND</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="wrap-btn-cmt" v-if="!video.flagComment">
                    <div class="block-btn">
                        <button class="btn-cmt"><i class="fa-regular fa-eye"></i></button>
                        <span class="text-label">{{ video?.total_views || 0 }} lượt xem</span>
                    </div>
                    <div class="block-btn">
                        <button @click="fetchCommentByID(video)" class="btn-cmt"><i
                                class="fa-solid fa-comment-medical"></i></button>
                        <span class="text-label">{{ video?.total_comments || 0 }} bình luận</span>
                    </div>
                    <div class="block-btn" v-if="video.linkShare">
                        <button @click="copyLink(video)" class="btn-cmt"><i class="fa-solid fa-share"></i></button>
                        <span class="text-label">Copy link</span>
                        <div v-if="video.isCopied" class="copied">Đã copy</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p v-if="videos.length <= 0" style="text-align: center; font-size: 25px; font-weight: bold">Chưa có video!
    </p>
    <a v-if="idVideoUrl" class="load-more-video"
        href="https://bitrix.esuhai.org/page-custom/module-video/list-video">Xem
        thêm video</a>
    <!--Phân trang -->
    <div v-if="totalPages > 1" class="pagination">
        <button @click="changePage(page)" v-for="page in totalPages" :key="page"
            :class="{ 'active': page === currentPage }">
            {{ page }}
        </button>
    </div>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { format } from 'date-fns';
import useAuth from '../composables/useAuth'
const { user, token, ensureUserLoaded } = useAuth()


// Data
const userFullName = ref()
const userID = ref()
const idVideoUrl = ref()
const videos = ref([])
const videoElements = ref([])
const loading = ref(false)

const listTopic = [
    { value: 'Tất cả', label: 'Tất cả' },
    {
        value: 'Coaching MS', label: 'Coaching MS',
        children: [
            { value: 'Chương trình tham gia', label: 'Chương trình tham gia' },
            { value: 'Chi phí tham gia', label: 'Chi phí tham gia' },
            { value: 'Điều kiện tham gia', label: 'Điều kiện tham gia' },
            { value: 'Lương & Chế độ phúc lợi', label: 'Lương & Chế độ phúc lợi' },
            { value: 'Định hướng tương lai', label: 'Định hướng tương lai' },
            { value: 'Định hướng nghề nghiệp', label: 'Định hướng nghề nghiệp' },
            { value: 'Đào tạo Tiếng Nhật', label: 'Đào tạo Tiếng Nhật' },
            { value: 'Hỗ trợ từ Esuhai', label: 'Hỗ trợ từ Esuhai' }
        ]
    },
    {
        value: 'Cẩm nang Tư vấn', label: 'Cẩm nang Tư vấn',
        children: [
            { value: 'Chương trình tham gia', label: 'Chương trình tham gia' },
            { value: 'Chi phí tham gia', label: 'Chi phí tham gia' },
            { value: 'Điều kiện tham gia', label: 'Điều kiện tham gia' },
            { value: 'Ngành nghề làm việc', label: 'Ngành nghề làm việc' },
            { value: 'Lương & Chế độ phúc lợi', label: 'Lương & Chế độ phúc lợi' },
            { value: 'Đào tạo từ Esuhai', label: 'Đào tạo từ Esuhai' },
            { value: 'Hỗ trợ từ Esuhai', label: 'Hỗ trợ từ Esuhai' },
            { value: 'Định hướng tương lai', label: 'Định hướng tương lai' }
        ]
    },
    { value: 'Chia sẻ Nhân viên', label: 'Chia sẻ Nhân viên' },
    { value: 'Thầy Sơn - CareerCoach', label: 'Thầy Sơn - CareerCoach' },
    { value: 'Tôi vẽ tương lai', label: 'Tôi vẽ tương lai' }
]

const paramVideo = reactive({
    id: idVideoUrl.value,
    hashtag: '',
    topic: '',
    searchInput: ''
})

const currentPage = ref(1);
const itemsPerPage = ref(10); // Number of videos per page
const totalVideos = ref(0);

const totalPages = computed(() => Math.ceil(totalVideos.value / itemsPerPage.value));
const changePage = (page) => {
    currentPage.value = page;
    fetchVideos();
};

const copyLink = (video) => {
    if (video?.linkShare) {
        navigator.clipboard.writeText(video?.linkShare).then(() => {
            video.isCopied = true;
            setTimeout(() => {
                video.isCopied = false;
            }, 1500);
        });
    }
}

const fetchVideos = async () => {
    loading.value = true
    try {
        const response = await axios.get(`api/videos`, {
            params: {
                ...paramVideo,
                page: currentPage.value,
                limit: itemsPerPage.value
            }
        });

        videos.value = response.data.videos;
        totalVideos.value = response.data.total;
        await nextTick();
        // Collect all video elements
        videoElements.value = Array.from(document.querySelectorAll('video'));
    } catch (error) {
        console.error("An error occurred: ", error);
    } finally {
        loading.value = false;
    }
}
const closeComment = (video) => {
    video.flagComment = false;
}
const fetchCommentByID = async (video) => {
    video.flagComment = true;
    const idVideo = video.id;
    try {
        const response = await axios.get('api/comments', {
            params: {
                id: idVideo
            }
        });
        if (response.data.length > 0) {
            // selectedVideo.value = response.data[0];
            video.comments = response.data;
        } else {
            console.warn(`No comment found with ID: ${idVideo}`);
        }
    } catch (error) {
        console.error("An error occurred: ", error);
    }
}
const handleSubmit = async (video, type) => {
    console.log(video, type);
    try {
        // Build payload from authenticated user
        const comment = {
            username: user.value?.name || user.value?.username || userFullName.value || '',
            message: type === 'comment' ? video.myComment : '',
            user_id: Number(user.value?.bitrix_user_id ?? userID.value),
            video_id: Number(video.id),
            type
        }
        const response = await axios.post('api/comments', comment);
        if (response.data.message && type == 'comment') {
            video.myComment = '';
            await fetchCommentByID(video);
        }
    } catch (error) {
        console.error("An error occurred: ", error);
    } finally {
        loading.value = false;
    }
};
const filterHash = (hash) => {
    paramVideo.topic = '';
    paramVideo.hashtag = hash;
    paramVideo.id = null;
    currentPage.value = 1;
    paramVideo.searchInput = '';
    fetchVideos();
}
const filterTopic = (topic) => {
    paramVideo.topic = topic == 'Tất cả' ? '' : topic;
    paramVideo.hashtag = '';
    paramVideo.id = null;
    currentPage.value = 1;
    paramVideo.searchInput = '';
    fetchVideos();
}

const handlePlay = (video, event) => {
    // Pause all other videos
    videoElements.value.forEach(videoElement => {
        if (videoElement !== event.target) {
            videoElement.pause();
        }
    });

    // ghi log lại view video
    handleSubmit(video, 'view');
};
const formatDate = (dateString) => {
    return format(new Date(dateString), "dd MMM HH:mm");
};

// "giải mã" chuỗi HTML escaped này trước khi chèn vào v-html.
const decodeHTML = (html) => {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

const playVideoIframe = (video) => {
    handleSubmit(video, 'view');
    video.isPlaying = !video.isPlaying;
}

const handleSearch = () => {
    paramVideo.topic = '';
    paramVideo.hashtag = '';
    paramVideo.id = null;
    currentPage.value = 1;
    console.log(paramVideo.searchInput);
    paramVideo.searchInput = paramVideo.searchInput ? paramVideo.searchInput
        .trim() : '';
    fetchVideos();
}


onMounted(async () => {
    await ensureUserLoaded()
    console.log(user.value);

    fetchVideos();
});

</script>