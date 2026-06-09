<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-gray-100">New Message</h1>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-6 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">To</label>
          <input
            v-model="form.receiver"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.receiver" class="text-red-400 text-xs mt-1">{{ form.errors.receiver }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Subject</label>
          <input
            v-model="form.subject"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.subject" class="text-red-400 text-xs mt-1">{{ form.errors.subject }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Message</label>
          <textarea
            v-model="form.body"
            rows="6"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="form.errors.body" class="text-red-400 text-xs mt-1">{{ form.errors.body }}</p>
        </div>
        <div class="flex gap-3">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-6 py-2 rounded font-semibold"
          >
            {{ form.processing ? 'Sending…' : 'Send' }}
          </button>
          <Link href="/messages" class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded font-semibold">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  receiver: { type: String, default: '' },
})

const form = useForm({
  receiver: props.receiver,
  subject: '',
  body: '',
})

function submit() {
  form.post(route('messages.store'))
}
</script>
