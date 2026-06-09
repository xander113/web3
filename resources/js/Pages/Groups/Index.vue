<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Groups</h1>
      <Link v-if="auth.user" :href="route('groups.create')"
            class="btn-flat px-4 py-2 text-white text-sm"
            style="background:#1b6182; text-decoration:none;">
        + New Group
      </Link>
    </div>

    <div v-if="groups.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link
        v-for="group in groups.data"
        :key="group.id"
        :href="route('groups.show', group.id)"
        class="gt-panel hover:border-[#1b6182] transition block"
        style="text-decoration:none;"
      >
        <!-- Group header -->
        <div class="flex items-center gap-3 mb-3">
          <div class="w-12 h-12 shrink-0 flex items-center justify-center"
               style="background:#222; border:1px solid #444;">
            <svg class="w-6 h-6" style="color:#1b6182;" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/>
            </svg>
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-gray-100 truncate">{{ group.name }}</p>
            <p class="text-xs text-gray-500">
              {{ group.members_count ?? group.member_count ?? 0 }} member{{ (group.members_count ?? group.member_count ?? 0) !== 1 ? 's' : '' }}
            </p>
          </div>
        </div>

        <p v-if="group.description" class="text-sm text-gray-400 truncate mb-2">{{ group.description }}</p>

        <p class="text-xs text-gray-600">
          By <span style="color:#17BCCF;">{{ group.creator?.username }}</span>
        </p>
      </Link>
    </div>

    <div v-else class="gt-panel text-center py-12">
      <p class="text-gray-500 mb-4">No groups yet.</p>
      <Link v-if="auth.user" :href="route('groups.create')"
            class="btn-flat px-6 py-2 text-white"
            style="background:#1b6182; text-decoration:none;">
        Create the first group
      </Link>
    </div>

    <!-- Pagination -->
    <div v-if="groups.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in groups.links"
        :key="link.label"
        :href="link.url ?? '#'"
        :class="['px-3 py-1 text-sm btn-flat', link.active ? 'text-white' : 'text-gray-400 hover:text-white']"
        :style="link.active ? 'background:#1b6182;' : 'background:#333;'"
        v-html="link.label"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

defineProps({ groups: { type: Object, required: true } })

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
</script>
