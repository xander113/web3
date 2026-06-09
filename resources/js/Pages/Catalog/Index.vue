<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-100">Catalog</h1>
      <Link v-if="auth.user" :href="route('catalog.upload')" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm font-semibold">
        Upload Item
      </Link>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2 mb-4">
      <Link
        v-for="tab in types"
        :key="tab.value"
        :href="route('catalog.index', { type: tab.value, search: searchQuery })"
        :class="[
          'px-3 py-1 rounded text-sm font-medium transition',
          currentType === tab.value ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
        ]"
      >{{ tab.label }}</Link>
    </div>

    <!-- Search -->
    <div class="mb-6">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search items…"
        @input="doSearch"
        class="bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 w-full sm:w-72 focus:outline-none focus:border-indigo-500"
      />
    </div>

    <!-- Items grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
      <Link
        v-for="item in items.data"
        :key="item.id"
        :href="route('catalog.show', item.id)"
        class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 flex flex-col gap-2 transition"
      >
        <div class="aspect-square bg-gray-700 rounded flex items-center justify-center text-gray-500 text-xs">
          <span v-if="!item.image_url">No image</span>
          <img v-else :src="item.image_url" :alt="item.name" class="w-full h-full object-cover rounded" />
        </div>
        <p class="text-sm font-medium text-gray-100 truncate">{{ item.name }}</p>
        <p class="text-xs text-gray-400 capitalize">{{ item.type }}</p>
        <p class="text-sm font-semibold text-yellow-400">
          {{ item.price === 0 ? 'Free' : `${item.price} coins` }}
        </p>
        <p class="text-xs text-gray-500 truncate">by {{ item.creator?.username ?? 'Graphictoria' }}</p>
      </Link>
    </div>

    <!-- Pagination -->
    <div v-if="items.links" class="flex justify-center gap-2">
      <Link
        v-for="link in items.links"
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
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  items: { type: Object, required: true },
  type: { type: String, default: '' },
  search: { type: String, default: '' },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
const currentType = ref(props.type)
const searchQuery = ref(props.search)

const types = [
  { label: 'All', value: '' },
  { label: 'Hats', value: 'hat' },
  { label: 'Heads', value: 'head' },
  { label: 'Faces', value: 'face' },
  { label: 'Shirts', value: 'shirt' },
  { label: 'Pants', value: 'pants' },
  { label: 'T-Shirts', value: 'tshirt' },
  { label: 'Gear', value: 'gear' },
  { label: 'Decals', value: 'decal' },
]

let debounceTimer = null
function doSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('catalog.index'), { type: currentType.value, search: searchQuery.value }, { preserveState: true, replace: true })
  }, 400)
}
</script>
