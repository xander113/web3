<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-gray-100">Create Game Server</h1>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-6 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">IP Address <span class="text-gray-600">(optional)</span></label>
          <input
            v-model="form.ip"
            type="text"
            placeholder="e.g. 192.168.1.100"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.ip" class="text-red-400 text-xs mt-1">{{ form.errors.ip }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Port <span class="text-gray-600">(optional)</span></label>
          <input
            v-model.number="form.port"
            type="number"
            placeholder="e.g. 53640"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.port" class="text-red-400 text-xs mt-1">{{ form.errors.port }}</p>
        </div>
        <div class="flex items-center gap-3">
          <input
            v-model="form.is_public"
            type="checkbox"
            id="is_public"
            class="accent-indigo-500 w-4 h-4"
          />
          <label for="is_public" class="text-sm text-gray-300">Public server (visible to all users)</label>
        </div>
        <p v-if="form.errors.is_public" class="text-red-400 text-xs">{{ form.errors.is_public }}</p>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Creating…' : 'Create Server' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({
  name: '',
  description: '',
  ip: '',
  port: null,
  is_public: true,
})

function submit() {
  form.post(route('games.store'))
}
</script>
