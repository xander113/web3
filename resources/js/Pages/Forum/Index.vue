<template>
  <AppLayout>
    <h1 class="text-2xl font-bold mb-6 text-gray-100">Forum</h1>

    <div class="space-y-6">
      <div v-for="category in categories" :key="category.id" class="bg-gray-800 rounded-xl overflow-hidden">
        <div class="px-5 py-3 bg-gray-750 border-b border-gray-700">
          <h2 class="text-lg font-semibold text-indigo-400">{{ category.name }}</h2>
          <p v-if="category.description" class="text-sm text-gray-400">{{ category.description }}</p>
        </div>
        <div class="divide-y divide-gray-700">
          <Link
            v-for="forum in category.forums"
            :key="forum.id"
            :href="route('forum.show', forum.id)"
            class="flex items-center justify-between px-5 py-4 hover:bg-gray-700 transition"
          >
            <div>
              <p class="font-medium text-gray-100">{{ forum.name }}</p>
              <p v-if="forum.description" class="text-sm text-gray-400">{{ forum.description }}</p>
            </div>
            <div class="text-right text-xs text-gray-500 shrink-0 ml-6">
              <p>{{ forum.topics_count ?? 0 }} topics</p>
              <p>{{ forum.posts_count ?? 0 }} posts</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

defineProps({
  categories: { type: Array, default: () => [] },
})
</script>
