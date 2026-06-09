<template>
  <AppLayout>
    <div class="mb-6">
      <Link href="/admin" class="text-indigo-400 hover:underline text-sm">&larr; Admin</Link>
      <h1 class="text-2xl font-bold text-gray-100 mt-1">Pending Items</h1>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <div v-if="items.data && items.data.length">
        <table class="w-full text-sm">
          <thead class="bg-gray-700 text-gray-400 text-left">
            <tr>
              <th class="px-4 py-3">Item</th>
              <th class="px-4 py-3">Type</th>
              <th class="px-4 py-3">Creator</th>
              <th class="px-4 py-3">Price</th>
              <th class="px-4 py-3">Submitted</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-750">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center text-xs text-gray-500">
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="w-full h-full object-cover rounded" />
                    <span v-else>img</span>
                  </div>
                  <span class="font-medium text-gray-100">{{ item.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-400 capitalize">{{ item.type }}</td>
              <td class="px-4 py-3">
                <Link :href="route('profile.show', item.creator?.username)" class="text-indigo-400 hover:underline">
                  {{ item.creator?.username ?? 'Unknown' }}
                </Link>
              </td>
              <td class="px-4 py-3 text-gray-300">{{ item.price === 0 ? 'Free' : `${item.price} coins` }}</td>
              <td class="px-4 py-3 text-gray-400">{{ formatDate(item.created_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <form @submit.prevent="approveItem(item.id)">
                    <button class="bg-green-700 hover:bg-green-600 text-white text-xs px-3 py-1 rounded">Approve</button>
                  </form>
                  <form @submit.prevent="declineItem(item.id)">
                    <button class="bg-red-800 hover:bg-red-700 text-red-200 text-xs px-3 py-1 rounded">Decline</button>
                  </form>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-center text-gray-500 py-12">No pending items.</p>
    </div>

    <!-- Pagination -->
    <div v-if="items.links" class="flex justify-center gap-2 mt-6">
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
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

defineProps({
  items: { type: Object, required: true },
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const actionForm = useForm({})
function approveItem(id) {
  actionForm.patch(route('admin.assets.approve', id))
}
function declineItem(id) {
  actionForm.delete(route('admin.assets.decline', id))
}
</script>
