<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-6" style="color:#e8e8e8;">Create Game Server</h1>
      <form @submit.prevent="submit" class="gt-panel space-y-5">

        <div>
          <label class="block text-sm mb-1" style="color:#aaa;">Name</label>
          <input v-model="form.name" type="text"
            class="w-full px-3 py-2 text-sm"
            style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
          <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm mb-1" style="color:#aaa;">Description</label>
          <textarea v-model="form.description" rows="3"
            class="w-full px-3 py-2 text-sm resize-none"
            style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;"></textarea>
          <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
        </div>

        <!-- Place file: the game client loads this .rbxm to render the level -->
        <div>
          <label class="block text-sm mb-1" style="color:#aaa;">
            Place File <span style="color:#17BCCF;">(.rbxm)</span>
            <span class="ml-1 text-xs" style="color:#666;">The game client loads this file to render the world</span>
          </label>
          <input
            type="file"
            accept=".rbxm,.rbxl,.rbxlx"
            @change="onPlaceFile"
            class="block w-full text-sm py-1"
            style="color:#aaa;"
          />
          <p v-if="form.errors.place_file" class="text-red-400 text-xs mt-1">{{ form.errors.place_file }}</p>
          <p v-if="placeFileName" class="text-xs mt-1" style="color:#17BCCF;">Selected: {{ placeFileName }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm mb-1" style="color:#aaa;">Server IP <span style="color:#666;">(optional)</span></label>
            <input v-model="form.ip" type="text" placeholder="e.g. 192.168.1.100"
              class="w-full px-3 py-2 text-sm"
              style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
            <p v-if="form.errors.ip" class="text-red-400 text-xs mt-1">{{ form.errors.ip }}</p>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color:#aaa;">Port <span style="color:#666;">(optional)</span></label>
            <input v-model.number="form.port" type="number" placeholder="e.g. 53640"
              class="w-full px-3 py-2 text-sm"
              style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
            <p v-if="form.errors.port" class="text-red-400 text-xs mt-1">{{ form.errors.port }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <input v-model="form.is_public" type="checkbox" id="is_public" class="w-4 h-4" style="accent-color:#1b6182;" />
          <label for="is_public" class="text-sm" style="color:#ccc;">Public server (visible to all users)</label>
        </div>

        <button type="submit" :disabled="form.processing"
          class="w-full btn-flat py-2 text-white font-semibold text-sm"
          style="background:#1b6182;">
          {{ form.processing ? 'Creating…' : 'Create Server' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const placeFileName = ref('')

const form = useForm({
  name:        '',
  description: '',
  ip:          '',
  port:        null,
  is_public:   true,
  place_file:  null,
})

function onPlaceFile(e) {
  const file = e.target.files[0] ?? null
  form.place_file = file
  placeFileName.value = file ? file.name : ''
}

function submit() {
  form.post(route('games.store'), { forceFormData: true })
}
</script>
