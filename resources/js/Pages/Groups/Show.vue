<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-100">{{ group.name }}</h1>
            <p class="text-sm text-gray-400 mt-1">
              Created by
              <Link :href="route('profile.show', group.owner?.id)" class="text-indigo-400 hover:underline">{{ group.owner?.username }}</Link>
            </p>
            <p class="text-gray-500 text-sm">{{ group.members_count ?? group.members?.length ?? 0 }} members</p>
          </div>
          <div v-if="auth.user">
            <form v-if="isMember" @submit.prevent="leaveGroup">
              <button class="bg-red-800 hover:bg-red-700 text-red-200 px-4 py-2 rounded text-sm font-semibold">Leave Group</button>
            </form>
            <form v-else @submit.prevent="joinGroup">
              <button class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm font-semibold">Join Group</button>
            </form>
          </div>
        </div>
        <p v-if="group.description" class="text-gray-300 mt-4 leading-relaxed">{{ group.description }}</p>
      </div>

      <!-- Members -->
      <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 text-indigo-400">Members</h2>
        <div v-if="group.members && group.members.length" class="grid grid-cols-3 sm:grid-cols-5 gap-3">
          <Link
            v-for="member in group.members"
            :key="member.id"
            :href="route('profile.show', member.id)"
            class="flex flex-col items-center gap-1 hover:opacity-80"
          >
            <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center font-bold">
              {{ member.username[0].toUpperCase() }}
            </div>
            <span class="text-xs text-gray-400 truncate w-full text-center">{{ member.username }}</span>
          </Link>
        </div>
        <p v-else class="text-gray-500">No members yet.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  group: { type: Object, required: true },
  isMember: { type: Boolean, default: false },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })

const groupForm = useForm({})
function joinGroup() {
  groupForm.post(route('groups.join', props.group.id))
}
function leaveGroup() {
  groupForm.delete(route('groups.leave', props.group.id))
}
</script>
