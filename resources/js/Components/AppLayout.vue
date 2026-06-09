<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const mobileOpen = ref(false);
const dropdownOpen = ref(false);
</script>

<template>
  <div class="min-h-screen bg-gray-900 text-gray-100">
    <!-- Nav -->
    <nav class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center gap-8">
            <Link :href="route('home')" class="text-2xl font-bold text-indigo-400 hover:text-indigo-300 transition">
              Graphictoria
            </Link>
            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-1">
              <Link :href="route('home')" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">Home</Link>
              <Link :href="route('catalog.index')" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">Catalog</Link>
              <Link :href="route('forum.index')" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">Forum</Link>
              <Link :href="route('users.index')" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">Users</Link>
            </div>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-4">
            <template v-if="user">
              <!-- Coins -->
              <span class="hidden sm:flex items-center gap-1 text-yellow-400 font-medium text-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
                {{ user.coins }}
              </span>

              <!-- Messages -->
              <Link :href="route('messages.index')" class="relative p-2 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span v-if="user.unread_messages > 0" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ user.unread_messages }}</span>
              </Link>

              <!-- Friend Requests -->
              <Link :href="route('friends.index')" class="relative p-2 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span v-if="user.pending_requests > 0" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ user.pending_requests }}</span>
              </Link>

              <!-- User dropdown -->
              <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition">
                  {{ user.username }}
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div v-if="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-xl border border-gray-700 py-1 z-50">
                  <Link :href="route('profile.show', user.id)" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</Link>
                  <Link :href="route('character.index')" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Character</Link>
                  <Link :href="route('settings')" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Settings</Link>
                  <Link v-if="user.rank >= 1" :href="route('admin.index')" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700">Admin Panel</Link>
                  <hr class="border-gray-700 my-1">
                  <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Logout</Link>
                </div>
              </div>
            </template>
            <template v-else>
              <Link :href="route('login')" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Login</Link>
              <Link :href="route('register')" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition">Register</Link>
            </template>

            <!-- Mobile toggle -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
          </div>
        </div>

        <!-- Mobile menu -->
        <div v-if="mobileOpen" class="md:hidden pb-4 space-y-1">
          <Link :href="route('home')" class="block px-3 py-2 rounded-md text-gray-300 hover:bg-gray-700">Home</Link>
          <Link :href="route('catalog.index')" class="block px-3 py-2 rounded-md text-gray-300 hover:bg-gray-700">Catalog</Link>
          <Link :href="route('forum.index')" class="block px-3 py-2 rounded-md text-gray-300 hover:bg-gray-700">Forum</Link>
          <Link :href="route('users.index')" class="block px-3 py-2 rounded-md text-gray-300 hover:bg-gray-700">Users</Link>
        </div>
      </div>
    </nav>

    <!-- Flash Messages -->
    <div v-if="flash.success || flash.error" class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="flash.success" class="flex items-center gap-3 bg-green-900/50 border border-green-600 text-green-300 px-4 py-3 rounded-lg">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="flex items-center gap-3 bg-red-900/50 border border-red-600 text-red-300 px-4 py-3 rounded-lg">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        {{ flash.error }}
      </div>
    </div>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 mt-16 py-8">
      <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
        <p>&copy; {{ new Date().getFullYear() }} Graphictoria. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
