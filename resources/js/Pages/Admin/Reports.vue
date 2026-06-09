<template>
  <AppLayout>
    <div class="mb-6">
      <Link href="/admin" class="text-indigo-400 hover:underline text-sm">&larr; Admin</Link>
      <h1 class="text-2xl font-bold text-gray-100 mt-1">Reports</h1>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <div v-if="reports.data && reports.data.length">
        <table class="w-full text-sm">
          <thead class="bg-gray-700 text-gray-400 text-left">
            <tr>
              <th class="px-4 py-3">Reporter</th>
              <th class="px-4 py-3">Reported</th>
              <th class="px-4 py-3">Reason</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-750">
              <td class="px-4 py-3">
                <Link :href="route('profile.show', report.reporter?.id)" class="text-indigo-400 hover:underline">
                  {{ report.reporter?.username ?? 'Unknown' }}
                </Link>
              </td>
              <td class="px-4 py-3">
                <Link :href="route('profile.show', report.reportedUser?.id)" class="text-indigo-400 hover:underline">
                  {{ report.reportedUser?.username ?? 'Unknown' }}
                </Link>
              </td>
              <td class="px-4 py-3 text-gray-300 max-w-xs">
                <p class="truncate">{{ report.reason }}</p>
              </td>
              <td class="px-4 py-3 text-gray-400">{{ formatDate(report.created_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <form @submit.prevent="resolveReport(report.id)">
                    <button class="bg-green-700 hover:bg-green-600 text-white text-xs px-3 py-1 rounded">Resolve</button>
                  </form>
                  <form @submit.prevent="dismissReport(report.id)">
                    <button class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-1 rounded">Dismiss</button>
                  </form>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-center text-gray-500 py-12">No open reports.</p>
    </div>

    <!-- Pagination -->
    <div v-if="reports.links" class="flex justify-center gap-2 mt-6">
      <Link
        v-for="link in reports.links"
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
  reports: { type: Object, required: true },
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const resolveForm = useForm({ status: 'reviewed' })
const dismissForm = useForm({ status: 'dismissed' })

function resolveReport(id) {
  resolveForm.post(route('admin.reports.resolve', id))
}
function dismissReport(id) {
  dismissForm.post(route('admin.reports.dismiss', id))
}
</script>
