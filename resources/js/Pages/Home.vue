<template>
  <AppLayout>
    <!-- Toast notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="bg-indigo-700 text-white px-4 py-3 rounded shadow-lg max-w-xs"
      >
        <p class="font-semibold text-sm">{{ toast.title }}</p>
        <p class="text-xs text-indigo-200">{{ toast.body }}</p>
      </div>
    </div>

    <!-- Hero / Welcome -->
    <div class="mb-8">
      <template v-if="auth.user">
        <h1 class="text-3xl font-bold text-indigo-400">Welcome back, {{ auth.user.username }}!</h1>
        <p class="text-gray-400 mt-1">Good to see you again.</p>
      </template>
      <template v-else>
        <div class="bg-gray-800 rounded-xl p-10 text-center">
          <h1 class="text-4xl font-bold text-indigo-400 mb-3">Welcome to Graphictoria</h1>
          <p class="text-gray-400 mb-6">The classic Roblox-inspired community. Build, play, and connect.</p>
          <div class="flex justify-center gap-4">
            <Link href="/login" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded font-semibold">Login</Link>
            <Link href="/register" class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded font-semibold">Register</Link>
          </div>
        </div>
      </template>
    </div>

    <!-- Online Now -->
    <div>
      <h2 class="text-xl font-semibold mb-4 text-gray-200">Online Now ({{ onlineList.length }})</h2>
      <div v-if="onlineList.length" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
        <Link
          v-for="user in onlineList"
          :key="user.id"
          :href="route('profile.show', user.id)"
          class="bg-gray-800 hover:bg-gray-700 rounded-lg p-3 flex flex-col items-center gap-2 transition"
        >
          <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-lg font-bold">
            {{ user.username[0].toUpperCase() }}
          </div>
          <span class="text-sm text-gray-200 truncate w-full text-center">{{ user.username }}</span>
          <span class="w-2 h-2 rounded-full bg-green-400"></span>
        </Link>
      </div>
      <p v-else class="text-gray-500">No users currently online.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  onlineUsers: { type: Array, default: () => [] },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })

const onlineList = ref([...props.onlineUsers])
const toasts = ref([])
let toastId = 0

function addToast(title, body) {
  const id = ++toastId
  toasts.value.push({ id, title, body })
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 4000)
}

let presenceChannel = null
let userChannel = null

onMounted(() => {
  if (!window.Echo) return

  // Presence channel for online users
  presenceChannel = window.Echo.channel('presence')
  presenceChannel.listen('UserPresenceUpdated', (e) => {
    if (e.online) {
      if (!onlineList.value.find((u) => u.id === e.user.id)) {
        onlineList.value.push(e.user)
      }
    } else {
      onlineList.value = onlineList.value.filter((u) => u.id !== e.user.id)
    }
  })

  // Private user channel for notifications
  if (auth.value.user) {
    userChannel = window.Echo.private(`user.${auth.value.user.id}`)
    userChannel.listen('MessageSent', (e) => {
      addToast('New Message', `From ${e.sender}: ${e.subject}`)
    })
    userChannel.listen('FriendRequestSent', (e) => {
      addToast('Friend Request', `${e.from_username} sent you a friend request`)
    })
  }
})

onUnmounted(() => {
  if (presenceChannel) window.Echo.leaveChannel('presence')
  if (userChannel && auth.value.user) window.Echo.leaveChannel(`private-user.${auth.value.user.id}`)
})
</script>
