<template>
  <AppLayout>
    <h1 class="text-2xl font-bold mb-6 text-gray-100">Friends</h1>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-gray-700">
      <button
        @click="activeTab = 'friends'"
        :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px transition', activeTab === 'friends' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-200']"
      >
        Friends ({{ friends.length }})
      </button>
      <button
        @click="activeTab = 'requests'"
        :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px transition', activeTab === 'requests' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-200']"
      >
        Requests ({{ requestList.length }})
      </button>
    </div>

    <!-- Friends list -->
    <div v-if="activeTab === 'friends'">
      <div v-if="friends.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="friend in friends"
          :key="friend.id"
          class="bg-gray-800 rounded-lg p-4 flex items-center justify-between gap-3"
        >
          <Link :href="route('profile.show', friend.username)" class="flex items-center gap-3 hover:opacity-80">
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold">
              {{ friend.username[0].toUpperCase() }}
            </div>
            <span class="text-gray-200 font-medium">{{ friend.username }}</span>
          </Link>
          <form @submit.prevent="removeFriend(friend.id)">
            <button class="bg-red-800 hover:bg-red-700 text-red-200 text-sm px-3 py-1 rounded">Remove</button>
          </form>
        </div>
      </div>
      <p v-else class="text-gray-500">You have no friends yet.</p>
    </div>

    <!-- Requests list -->
    <div v-if="activeTab === 'requests'">
      <div v-if="requestList.length" class="space-y-3">
        <div
          v-for="req in requestList"
          :key="req.id"
          class="bg-gray-800 rounded-lg p-4 flex items-center justify-between gap-3"
        >
          <Link :href="route('profile.show', req.username)" class="flex items-center gap-3 hover:opacity-80">
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold">
              {{ req.username[0].toUpperCase() }}
            </div>
            <span class="text-gray-200 font-medium">{{ req.username }}</span>
          </Link>
          <div class="flex gap-2">
            <form @submit.prevent="acceptRequest(req.id)">
              <button class="bg-green-700 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">Accept</button>
            </form>
            <form @submit.prevent="declineRequest(req.id)">
              <button class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm px-3 py-1 rounded">Decline</button>
            </form>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500">No pending friend requests.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  friends: { type: Array, default: () => [] },
  requests: { type: Array, default: () => [] },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
const activeTab = ref('friends')
const requestList = ref([...props.requests])

const actionForm = useForm({})

function removeFriend(userId) {
  actionForm.delete(route('friends.remove', userId), { preserveState: true })
}
function acceptRequest(userId) {
  actionForm.post(route('friends.accept', userId), {
    onSuccess: () => {
      requestList.value = requestList.value.filter((r) => r.id !== userId)
    },
  })
}
function declineRequest(userId) {
  actionForm.delete(route('friends.decline', userId), {
    onSuccess: () => {
      requestList.value = requestList.value.filter((r) => r.id !== userId)
    },
  })
}

let userChannel = null
onMounted(() => {
  if (!window.Echo || !auth.value.user) return
  userChannel = window.Echo.private(`user.${auth.value.user.id}`)
  userChannel.listen('FriendRequestSent', (e) => {
    if (!requestList.value.find((r) => r.id === e.from_id)) {
      requestList.value.push({ id: e.from_id, username: e.from_username })
    }
  })
})
onUnmounted(() => {
  if (userChannel && auth.value.user) window.Echo.leaveChannel(`private-user.${auth.value.user.id}`)
})
</script>
