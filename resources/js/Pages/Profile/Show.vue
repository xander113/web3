<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header card -->
      <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-indigo-600 flex items-center justify-center text-3xl font-bold">
              {{ profile.username[0].toUpperCase() }}
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-100">{{ profile.username }}</h1>
                <span v-if="profile.is_admin" class="bg-red-700 text-red-200 text-xs px-2 py-0.5 rounded">Admin</span>
                <span v-else-if="profile.is_mod" class="bg-yellow-700 text-yellow-200 text-xs px-2 py-0.5 rounded">Moderator</span>
              </div>
              <p class="text-gray-400 text-sm mt-1">Joined {{ formatDate(profile.created_at) }}</p>
              <p class="text-gray-500 text-sm">Last seen {{ formatDate(profile.last_seen_at) }}</p>
              <p class="text-gray-400 text-sm">{{ profile.post_count ?? 0 }} posts</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-2 shrink-0">
            <template v-if="isOwnProfile">
              <Link :href="route('settings.index')" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-sm text-center">Edit Profile</Link>
            </template>
            <template v-else-if="auth.user">
              <Link :href="route('messages.create', { receiver: profile.username })" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm text-center">Send Message</Link>
              <template v-if="receivedRequest">
                <form @submit.prevent="acceptRequest">
                  <button class="w-full bg-green-600 hover:bg-green-500 px-4 py-2 rounded text-sm">Accept Request</button>
                </form>
                <form @submit.prevent="declineRequest">
                  <button class="w-full bg-red-700 hover:bg-red-600 px-4 py-2 rounded text-sm">Decline</button>
                </form>
              </template>
              <template v-else-if="isFriend">
                <span class="bg-green-800 text-green-300 px-4 py-2 rounded text-sm text-center">Friends</span>
              </template>
              <template v-else-if="hasPendingRequest">
                <span class="bg-gray-700 text-gray-400 px-4 py-2 rounded text-sm text-center">Request Sent</span>
              </template>
              <template v-else>
                <form @submit.prevent="sendFriendRequest">
                  <button class="w-full bg-indigo-700 hover:bg-indigo-600 px-4 py-2 rounded text-sm">Add Friend</button>
                </form>
              </template>
            </template>
          </div>
        </div>

        <!-- Ban notice -->
        <div v-if="profile.banned_at" class="mt-4 bg-red-900/50 border border-red-700 rounded p-3 text-sm text-red-300">
          This user is banned. Reason: {{ profile.ban_reason ?? 'No reason given' }}
        </div>
      </div>

      <!-- About -->
      <div v-if="profile.about" class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-2 text-indigo-400">About</h2>
        <p class="text-gray-300 whitespace-pre-line">{{ profile.about }}</p>
      </div>

      <!-- Badges -->
      <div v-if="profile.badges && profile.badges.length" class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3 text-indigo-400">Badges</h2>
        <div class="flex flex-wrap gap-3">
          <div
            v-for="badge in profile.badges"
            :key="badge.id"
            class="bg-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200"
          >
            {{ badge.name }}
          </div>
        </div>
      </div>

      <!-- Friends -->
      <div v-if="profile.friends && profile.friends.length" class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold text-indigo-400">Friends</h2>
          <Link :href="route('friends.index')" class="text-sm text-indigo-400 hover:underline">View all</Link>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
          <Link
            v-for="friend in profile.friends.slice(0, 6)"
            :key="friend.id"
            :href="route('profile.show', friend.username)"
            class="flex flex-col items-center gap-1 hover:opacity-80"
          >
            <div class="w-12 h-12 rounded-full bg-indigo-700 flex items-center justify-center font-bold">
              {{ friend.username[0].toUpperCase() }}
            </div>
            <span class="text-xs text-gray-400 truncate w-full text-center">{{ friend.username }}</span>
          </Link>
        </div>
      </div>

      <!-- Groups -->
      <div v-if="profile.groups && profile.groups.length" class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3 text-indigo-400">Groups</h2>
        <div class="space-y-2">
          <Link
            v-for="group in profile.groups"
            :key="group.id"
            :href="route('groups.show', group.id)"
            class="block bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm text-gray-200"
          >
            {{ group.name }}
          </Link>
        </div>
      </div>

      <!-- Game Servers -->
      <div v-if="profile.gameServers && profile.gameServers.length" class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3 text-indigo-400">Game Servers</h2>
        <div class="space-y-2">
          <div
            v-for="server in profile.gameServers"
            :key="server.id"
            class="bg-gray-700 rounded px-3 py-2 text-sm text-gray-200"
          >
            {{ server.name }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  profile: { type: Object, required: true },
  isFriend: { type: Boolean, default: false },
  hasPendingRequest: { type: Boolean, default: false },
  receivedRequest: { type: Boolean, default: false },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
const isOwnProfile = computed(() => auth.value.user?.id === props.profile.id)

function formatDate(dateStr) {
  if (!dateStr) return 'Never'
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const friendForm = useForm({})

function sendFriendRequest() {
  friendForm.post(route('friends.request', props.profile.id))
}
function acceptRequest() {
  friendForm.post(route('friends.accept', props.profile.id))
}
function declineRequest() {
  friendForm.delete(route('friends.decline', props.profile.id))
}
</script>
