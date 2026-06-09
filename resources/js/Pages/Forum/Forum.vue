<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <div>
        <Link href="/forum" class="text-sm text-indigo-400 hover:underline">&larr; Forum</Link>
        <h1 class="text-2xl font-bold text-gray-100 mt-1">{{ forum.name }}</h1>
        <p v-if="forum.description" class="text-gray-400 text-sm">{{ forum.description }}</p>
      </div>
      <Link
        v-if="auth.user"
        :href="route('forum.topic.create', forum.id)"
        class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded font-semibold text-sm"
      >New Topic</Link>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <div v-if="topics.data && topics.data.length" class="divide-y divide-gray-700">
        <Link
          v-for="topic in topics.data"
          :key="topic.id"
          :href="route('forum.topic.show', topic.id)"
          class="flex items-center justify-between px-5 py-4 hover:bg-gray-700 transition"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div>
              <p class="font-medium text-gray-100 truncate">{{ topic.title }}</p>
              <p class="text-xs text-gray-500">
                by {{ topic.user?.username ?? 'Unknown' }}
              </p>
            </div>
            <span v-if="topic.is_locked" class="text-xs bg-red-900 text-red-300 px-2 py-0.5 rounded">Locked</span>
            <span v-if="topic.is_pinned" class="text-xs bg-yellow-900 text-yellow-300 px-2 py-0.5 rounded">Pinned</span>
          </div>
          <div class="text-right text-xs text-gray-500 shrink-0 ml-4">
            <p>{{ topic.replies_count ?? 0 }} replies</p>
            <p>{{ formatDate(topic.last_activity_at ?? topic.created_at) }}</p>
          </div>
        </Link>
      </div>
      <p v-else class="text-center text-gray-500 py-12">No topics yet. Be the first!</p>
    </div>

    <!-- Pagination -->
    <div v-if="topics.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in topics.links"
        :key="link.label"
        :href="link.url ?? '#'"
        :class="[
          'px-3 py-1 rounded text-sm',
          link.active ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
          !link.url ? 'opacity-40 pointer-events-none' : '',
        ]"
        v-html="link.label"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

defineProps({
  forum: { type: Object, required: true },
  topics: { type: Object, required: true },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>
