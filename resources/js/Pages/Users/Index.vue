<template>
  <AppLayout>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-100">Users</h1>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search users…"
        class="bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 w-64 focus:outline-none focus:border-indigo-500"
      />
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
      <Link
        v-for="user in users.data"
        :key="user.id"
        :href="route('profile.show', user.id)"
        class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 flex flex-col items-center gap-2 transition"
      >
        <div class="relative">
          <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center text-xl font-bold">
            {{ user.username[0].toUpperCase() }}
          </div>
          <span
            class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-gray-800"
            :class="user.is_online ? 'bg-green-400' : 'bg-gray-500'"
          ></span>
        </div>
        <span class="text-sm text-gray-200 truncate w-full text-center font-medium">{{ user.username }}</span>
        <span v-if="user.is_admin" class="text-xs bg-red-800 text-red-300 px-2 py-0.5 rounded">Admin</span>
        <span v-else-if="user.is_mod" class="text-xs bg-yellow-800 text-yellow-300 px-2 py-0.5 rounded">Mod</span>
      </Link>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center gap-2">
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
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
    router.get(route('users.index'), { search: val }, { preserveState: true, replace: true })
  }, 400)
})
</script>
