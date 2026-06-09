<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-2 text-gray-100">Create Group</h1>
      <div class="bg-yellow-900/40 border border-yellow-700 text-yellow-300 rounded px-4 py-3 text-sm mb-6">
        Creating a group costs <strong>50 coins</strong>.
      </div>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-6 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Group Name</label>
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
            rows="4"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Creating…' : 'Create Group (50 coins)' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({ name: '', description: '' })

function submit() {
  form.post(route('groups.store'))
}
</script>
