<template>
    <h1 class="text-2xl font-bold">Chào mừng đến với Inertia.js + Vue!</h1>
    <p>Count: {{ count }}</p>
    <button @click="increment">Tăng</button>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);

const increment = () => {
    count.value++;
};
</script>

