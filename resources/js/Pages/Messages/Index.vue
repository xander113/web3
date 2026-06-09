<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-100">Messages</h1>
      <Link href="/messages/create" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm font-semibold">New Message</Link>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <div v-if="messageList.length" class="divide-y divide-gray-700">
        <Link
          v-for="msg in messageList"
          :key="msg.id"
          :href="route('messages.show', msg.id)"
          class="flex items-center gap-4 px-5 py-4 hover:bg-gray-700 transition"
        >
          <div
            class="w-2 h-2 rounded-full shrink-0"
            :class="msg.read_at ? 'bg-gray-600' : 'bg-indigo-400'"
          ></div>
          <div class="flex-1 min-w-0">
            <p :class="['font-medium truncate', msg.read_at ? 'text-gray-400' : 'text-gray-100']">
              {{ msg.subject }}
            </p>
            <p class="text-xs text-gray-500">From: {{ msg.sender?.username ?? 'Unknown' }}</p>
          </div>
          <span class="text-xs text-gray-500 shrink-0">{{ formatDate(msg.created_at) }}</span>
        </Link>
      </div>
      <p v-else class="text-center text-gray-500 py-12">No messages.</p>
    </div>

    <!-- Pagination -->
    <div v-if="messages.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in messages.links"
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  messages: { type: Object, required: true },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
const messageList = ref([...(props.messages.data ?? [])])

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

let userChannel = null
onMounted(() => {
  if (!window.Echo || !auth.value.user) return
  userChannel = window.Echo.private(`user.${auth.value.user.id}`)
  userChannel.listen('MessageSent', (e) => {
    messageList.value.unshift(e.message)
  })
})
onUnmounted(() => {
  if (userChannel && auth.value.user) window.Echo.leaveChannel(`private-user.${auth.value.user.id}`)
})
</script>
