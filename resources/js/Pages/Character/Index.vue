<template>
  <AppLayout>
    <h1 class="text-2xl font-bold mb-6 text-gray-100">My Character</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Equipped items -->
      <div class="bg-gray-800 rounded-xl p-5">
        <h2 class="text-lg font-semibold mb-4 text-indigo-400">Currently Equipped</h2>
        <div v-if="equipped.length" class="space-y-2">
          <div
            v-for="item in equipped"
            :key="item.id"
            class="flex items-center justify-between bg-gray-700 rounded px-3 py-2"
          >
            <div>
              <p class="text-sm text-gray-200 font-medium">{{ item.item?.name ?? item.type }}</p>
              <p class="text-xs text-gray-500 capitalize">{{ item.type }}</p>
            </div>
            <form @submit.prevent="unequip(item.catalog_item_id)">
              <button class="text-xs text-red-400 hover:text-red-300">Unequip</button>
            </form>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm">Nothing equipped.</p>

        <!-- Colors -->
        <h3 class="text-base font-semibold mt-6 mb-3 text-indigo-400">Body Colors</h3>
        <div class="space-y-2">
          <div v-for="part in bodyParts" :key="part.key" class="flex items-center gap-3">
            <label class="text-sm text-gray-400 w-20 shrink-0 capitalize">{{ part.label }}</label>
            <input
              type="color"
              :value="colorValues[part.key]"
              @change="updateColor(part.key, $event.target.value)"
              class="w-8 h-8 rounded cursor-pointer border-0 bg-transparent"
            />
            <span class="text-xs text-gray-500">{{ colorValues[part.key] }}</span>
          </div>
        </div>
      </div>

      <!-- Owned items -->
      <div class="lg:col-span-2">
        <!-- Type tabs -->
        <div class="flex flex-wrap gap-2 mb-4">
          <button
            v-for="tab in typeTabs"
            :key="tab.value"
            @click="switchType(tab.value)"
            :class="[
              'px-3 py-1 rounded text-sm font-medium transition',
              currentType === tab.value ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
            ]"
          >{{ tab.label }}</button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div
            v-for="item in filteredItems"
            :key="item.id"
            class="bg-gray-800 rounded-lg p-3 flex flex-col gap-2"
          >
            <div class="aspect-square bg-gray-700 rounded flex items-center justify-center text-gray-500 text-xs">
              <img v-if="item.item?.data_file" :src="`/catalog/thumbnail/${item.item.type}/${item.item.data_file}.png`" :alt="item.item?.name" class="w-full h-full object-cover rounded" />
              <span v-else>No image</span>
            </div>
            <p class="text-xs font-medium text-gray-200 truncate">{{ item.item?.name }}</p>
            <form @submit.prevent="equip(item.catalog_item_id)">
              <button
                :class="[
                  'w-full text-xs py-1 rounded font-medium',
                  isEquipped(item.catalog_item_id)
                    ? 'bg-red-800 hover:bg-red-700 text-red-200'
                    : 'bg-indigo-600 hover:bg-indigo-500 text-white',
                ]"
              >{{ isEquipped(item.catalog_item_id) ? 'Unequip' : 'Equip' }}</button>
            </form>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="ownedItems.links" class="flex justify-center gap-2 mt-6">
          <Link
            v-for="link in ownedItems.links"
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
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  ownedItems: { type: Object, required: true },
  equipped: { type: Array, default: () => [] },
  colors: { type: Object, default: () => ({}) },
  currentType: { type: String, default: '' },
})

const currentType = ref(props.currentType)

const typeTabs = [
  { label: 'All', value: '' },
  { label: 'Hats', value: 'hat' },
  { label: 'Heads', value: 'head' },
  { label: 'Faces', value: 'face' },
  { label: 'Shirts', value: 'shirt' },
  { label: 'Pants', value: 'pants' },
  { label: 'T-Shirts', value: 'tshirt' },
  { label: 'Gear', value: 'gear' },
]

const bodyParts = [
  { key: 'head', label: 'Head' },
  { key: 'torso', label: 'Torso' },
  { key: 'left_arm', label: 'Left Arm' },
  { key: 'right_arm', label: 'Right Arm' },
  { key: 'left_leg', label: 'Left Leg' },
  { key: 'right_leg', label: 'Right Leg' },
]

// Extract just the hex color string for each body part (colors prop is keyed by type)
const colorValues = ref(
  Object.fromEntries(
    bodyParts.map(p => [
      p.key,
      (typeof props.colors[p.key] === 'object'
        ? props.colors[p.key]?.color
        : props.colors[p.key]) ?? '#FFCC99',
    ])
  )
)

// Filtering is done server-side via ?type= query param
const filteredItems = computed(() => props.ownedItems.data ?? [])

function switchType(type) {
  currentType.value = type
  router.get(route('character.index'), { type }, { preserveState: true, replace: true })
}

// Compare catalog_item_id not the equipped_items.id row id
function isEquipped(catalogItemId) {
  return props.equipped.some((e) => e.catalog_item_id === catalogItemId)
}

const actionForm = useForm({})
function equip(catalogItemId) {
  if (isEquipped(catalogItemId)) {
    actionForm.post(route('character.unequip', catalogItemId))
  } else {
    actionForm.post(route('character.equip', catalogItemId))
  }
}
function unequip(catalogItemId) {
  actionForm.post(route('character.unequip', catalogItemId))
}

let colorDebounce = null
function updateColor(part, value) {
  colorValues.value[part] = value
  clearTimeout(colorDebounce)
  colorDebounce = setTimeout(() => {
    // Convert to array format the server expects: [{type, color}, ...]
    const colorArray = bodyParts.map(p => ({ type: p.key, color: colorValues.value[p.key] }))
    router.patch(route('character.colors'), { colors: colorArray }, { preserveState: true })
  }, 600)
}
</script>
