<template>
  <AppLayout>
    <h1 class="text-2xl font-bold mb-6 text-gray-100">Admin Panel</h1>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
      <div v-for="stat in statCards" :key="stat.label" class="bg-gray-800 rounded-xl p-5 text-center">
        <p class="text-3xl font-bold text-indigo-400">{{ stats[stat.key] ?? 0 }}</p>
        <p class="text-sm text-gray-400 mt-1">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Quick links -->
    <h2 class="text-lg font-semibold mb-4 text-gray-200">Quick Links</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <Link
        v-for="link in quickLinks"
        :key="link.href"
        :href="link.href"
        class="bg-gray-800 hover:bg-gray-700 rounded-xl p-5 text-center transition"
      >
        <p class="font-semibold text-gray-200">{{ link.label }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ link.desc }}</p>
      </Link>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

defineProps({
  stats: { type: Object, default: () => ({}) },
})

const statCards = [
  { key: 'users', label: 'Total Users' },
  { key: 'banned', label: 'Banned Users' },
  { key: 'items', label: 'Catalog Items' },
  { key: 'pending_items', label: 'Pending Items' },
  { key: 'reports', label: 'Open Reports' },
]

const quickLinks = [
  { href: '/admin/users', label: 'Users', desc: 'Manage users & bans' },
  { href: '/admin/assets', label: 'Assets', desc: 'Review pending items' },
  { href: '/admin/reports', label: 'Reports', desc: 'View user reports' },
  { href: '/forum', label: 'Forum', desc: 'Moderate forums' },
]
</script>
