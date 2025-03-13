<template>
  <Head title="Form Submissions" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Form Submissions
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="submission in submissions.data" :key="submission.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ submission.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ submission.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ submission.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ submission.phone }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800': submission.status === 'received',
                            'bg-yellow-100 text-yellow-800': submission.status === 'pending'
                          }">
                      {{ submission.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ new Date(submission.created_at).toLocaleString() }}
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
              <Pagination :links="submissions.links" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  submissions: Object
});
</script>