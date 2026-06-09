<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Games</h1>
      <Link v-if="auth.user" :href="route('games.create')"
            class="btn-flat px-4 py-2 text-white text-sm"
            style="background:#1b6182; text-decoration:none;">
        + New Server
      </Link>
    </div>

    <div v-if="servers.data.length" class="space-y-3">
      <div
        v-for="server in servers.data"
        :key="server.id"
        class="gt-panel flex items-center gap-4"
      >
        <!-- Server icon placeholder -->
        <div class="w-16 h-16 shrink-0 flex items-center justify-center"
             style="background:#222; border:1px solid #444;">
          <svg class="w-8 h-8" style="color:#1b6182;" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8H6a1 1 0 000 2h7.382l-1.829 1.829A1 1 0 0013 13h1a1 1 0 00.707-.293l2-2a1 1 0 000-1.414l-2-2a1 1 0 00-.154-.187z"/>
          </svg>
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-100">{{ server.name }}</p>
          <p v-if="server.description" class="text-sm text-gray-400 truncate">{{ server.description }}</p>
          <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
            <span>By <Link :href="route('profile.show', server.creator?.id)" class="hover:text-white" style="text-decoration:none; color:#17BCCF;">{{ server.creator?.username }}</Link></span>
            <span>{{ server.players_count ?? 0 }} join{{ (server.players_count ?? 0) !== 1 ? 's' : '' }}</span>
            <span v-if="server.ip">{{ server.ip }}:{{ server.port }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 shrink-0">
          <Link v-if="auth.user" :href="route('games.launch', server.id)"
                class="btn-flat px-4 py-2 text-white text-sm"
                style="background:#1b6182; text-decoration:none;">
            Play
          </Link>
          <Link v-else :href="route('login')"
                class="btn-flat px-4 py-2 text-white text-sm"
                style="background:#333; text-decoration:none;">
            Login to Play
          </Link>
        </div>
      </div>
    </div>

    <div v-else class="gt-panel text-center py-12">
      <p class="text-gray-500 mb-4">No public game servers yet.</p>
      <Link v-if="auth.user" :href="route('games.create')"
            class="btn-flat px-6 py-2 text-white"
            style="background:#1b6182; text-decoration:none;">
        Create the first server
      </Link>
    </div>

    <!-- Pagination -->
    <div v-if="servers.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in servers.links"
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

defineProps({ servers: { type: Object, required: true } })

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
</script>
