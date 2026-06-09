<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <div class="bg-gray-800 rounded-xl p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-xl font-bold text-gray-100">{{ message.subject }}</h1>
            <p class="text-sm text-gray-400 mt-1">
              From:
              <Link :href="route('profile.show', message.sender?.id)" class="text-indigo-400 hover:underline">
                {{ message.sender?.username ?? 'Unknown' }}
              </Link>
              &rarr;
              <Link :href="route('profile.show', message.receiver?.id)" class="text-indigo-400 hover:underline">
                {{ message.receiver?.username ?? 'Unknown' }}
              </Link>
            </p>
            <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(message.created_at) }}</p>
          </div>
          <div class="flex gap-2 shrink-0">
            <Link
              :href="route('messages.create', message.sender?.id)"
              class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm font-semibold"
            >Reply</Link>
            <form @submit.prevent="deleteMessage">
              <button class="bg-red-800 hover:bg-red-700 text-red-200 px-4 py-2 rounded text-sm font-semibold">Delete</button>
            </form>
          </div>
        </div>
        <hr class="border-gray-700" />
        <div class="text-gray-300 whitespace-pre-line leading-relaxed">{{ message.body }}</div>
      </div>

      <div class="mt-4">
        <Link href="/messages" class="text-indigo-400 hover:underline text-sm">&larr; Back to messages</Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  message: { type: Object, required: true },
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}

const deleteForm = useForm({})
function deleteMessage() {
  if (!confirm('Delete this message?')) return
  deleteForm.delete(route('messages.destroy', props.message.id))
}
</script>
