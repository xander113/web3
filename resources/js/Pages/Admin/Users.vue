<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <div>
        <Link href="/admin" class="text-indigo-400 hover:underline text-sm">&larr; Admin</Link>
        <h1 class="text-2xl font-bold text-gray-100 mt-1">Manage Users</h1>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search users…"
        class="bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 w-64 focus:outline-none focus:border-indigo-500"
      />
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-700 text-gray-400 text-left">
          <tr>
            <th class="px-4 py-3">User</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Joined</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
          <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-750">
            <td class="px-4 py-3">
              <Link :href="route('profile.show', user.id)" class="text-indigo-400 hover:underline font-medium">
                {{ user.username }}
              </Link>
            </td>
            <td class="px-4 py-3 text-gray-400">{{ user.email }}</td>
            <td class="px-4 py-3 text-gray-400">{{ formatDate(user.created_at) }}</td>
            <td class="px-4 py-3">
              <span v-if="user.banned_at" class="bg-red-900 text-red-300 text-xs px-2 py-0.5 rounded">Banned</span>
              <span v-else class="bg-green-900 text-green-300 text-xs px-2 py-0.5 rounded">Active</span>
            </td>
            <td class="px-4 py-3">
              <div v-if="user.banned_at" class="flex items-center gap-2">
                <form @submit.prevent="unbanUser(user.id)">
                  <button class="bg-green-700 hover:bg-green-600 text-white text-xs px-3 py-1 rounded">Unban</button>
                </form>
              </div>
              <div v-else class="flex items-center gap-2">
                <button
                  @click="openBanModal(user)"
                  class="bg-red-800 hover:bg-red-700 text-red-200 text-xs px-3 py-1 rounded"
                >Ban</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="users.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in users.links"
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

    <!-- Ban Modal -->
    <div v-if="banModal.open" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-4">
        <h3 class="text-lg font-semibold text-gray-100">Ban {{ banModal.user?.username }}</h3>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Ban Reason</label>
          <input
            v-model="banForm.reason"
            type="text"
            placeholder="e.g. Violation of community rules"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="banForm.errors.reason" class="text-red-400 text-xs mt-1">{{ banForm.errors.reason }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Ban Duration</label>
          <select v-model="banForm.duration" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500">
            <option value="">Permanent</option>
            <option value="1">1 day</option>
            <option value="3">3 days</option>
            <option value="7">7 days</option>
            <option value="30">30 days</option>
          </select>
        </div>
        <div class="flex gap-3">
          <form @submit.prevent="submitBan">
            <button class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded font-semibold text-sm" :disabled="banForm.processing">
              {{ banForm.processing ? 'Banning…' : 'Confirm Ban' }}
            </button>
          </form>
          <button @click="banModal.open = false" class="bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded font-semibold text-sm">Cancel</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, reactive } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  users: { type: Object, required: true },
  search: { type: String, default: '' },
})

const searchQuery = ref(props.search)
let debounceTimer = null
watch(searchQuery, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('admin.users'), { search: val }, { preserveState: true, replace: true })
  }, 400)
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const banModal = reactive({ open: false, user: null })
const banForm = useForm({ reason: '', duration: '' })

function openBanModal(user) {
  banModal.user = user
  banModal.open = true
  banForm.reset()
}

function submitBan() {
  banForm.post(route('admin.users.ban', banModal.user.id), {
    onSuccess: () => { banModal.open = false },
  })
}

const unbanForm = useForm({})
function unbanUser(userId) {
  unbanForm.delete(route('admin.users.unban', userId))
}
</script>
