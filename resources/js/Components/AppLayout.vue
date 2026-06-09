<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const mobileOpen = ref(false);
const dropdownOpen = ref(false);

function navClass(routeName) {
  try {
    return page.url.startsWith('/' + routeName.split('.')[0]) ? 'nav-active' : '';
  } catch { return ''; }
}
</script>

<template>
  <div class="min-h-screen" style="background:#1a1a1a; color:#e8e8e8;">

    <!-- ── Navbar (mirrors original navbar-inverse + sidebar brand) ──── -->
    <nav style="background:#222222; border-bottom:2px solid #1b6182;" class="sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">

          <!-- Logo + primary nav -->
          <div class="flex items-center gap-6">
            <Link :href="route('home')"
                  style="font-size:1.3rem; font-weight:700; color:#17BCCF; letter-spacing:0.04em;"
                  class="hover:opacity-80 transition">
              Graphictoria
            </Link>

            <div class="hidden md:flex items-center">
              <Link :href="route('home')"
                    :class="['px-3 py-3 text-sm font-medium transition border-b-2 border-transparent hover:border-[#1b6182] hover:text-white text-gray-300', $page.url === '/' ? 'nav-active border-[#1b6182]' : '']"
                    style="text-decoration:none;">
                Home
              </Link>
              <Link :href="route('catalog.index')"
                    class="px-3 py-3 text-sm font-medium transition border-b-2 border-transparent hover:border-[#1b6182] hover:text-white text-gray-300"
                    style="text-decoration:none;">
                Catalog
              </Link>
              <Link :href="route('forum.index')"
                    class="px-3 py-3 text-sm font-medium transition border-b-2 border-transparent hover:border-[#1b6182] hover:text-white text-gray-300"
                    style="text-decoration:none;">
                Forum
              </Link>
              <Link :href="route('users.index')"
                    class="px-3 py-3 text-sm font-medium transition border-b-2 border-transparent hover:border-[#1b6182] hover:text-white text-gray-300"
                    style="text-decoration:none;">
                Users
              </Link>
            </div>
          </div>

          <!-- Right side icons + user menu -->
          <div class="flex items-center gap-3">
            <template v-if="user">
              <!-- Coins -->
              <span class="hidden sm:flex items-center gap-1 text-sm font-semibold" style="color:#f0c040;">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14a6 6 0 110-12 6 6 0 010 12z"/>
                </svg>
                {{ user.coins.toLocaleString() }}
              </span>

              <!-- Messages icon -->
              <Link :href="route('messages.index')" class="relative p-2 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span v-if="user.unread_messages > 0"
                      class="absolute -top-0.5 -right-0.5 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                      style="background:#c0392b; font-size:0.6rem;">
                  {{ user.unread_messages }}
                </span>
              </Link>

              <!-- Friend requests icon -->
              <Link :href="route('friends.index')" class="relative p-2 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span v-if="user.pending_requests > 0"
                      class="absolute -top-0.5 -right-0.5 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                      style="background:#c0392b; font-size:0.6rem;">
                  {{ user.pending_requests }}
                </span>
              </Link>

              <!-- Username dropdown -->
              <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium transition"
                        :class="user.rank === 1 ? 'rank-admin' : user.rank === 2 ? 'rank-moderator' : 'text-gray-300 hover:text-white'">
                  {{ user.username }}
                  <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>

                <div v-if="dropdownOpen"
                     @click.away="dropdownOpen = false"
                     class="absolute right-0 mt-1 w-48 shadow-xl z-50 py-1"
                     style="background:#2a2a2a; border:1px solid #383838; border-radius:2px;">
                  <Link :href="route('profile.show', user.id)"
                        class="block px-4 py-2 text-sm text-gray-300 hover:text-white transition"
                        style="text-decoration:none;"
                        @click="dropdownOpen = false">
                    My Profile
                  </Link>
                  <Link :href="route('character.index')"
                        class="block px-4 py-2 text-sm text-gray-300 hover:text-white transition"
                        style="text-decoration:none;"
                        @click="dropdownOpen = false">
                    Character
                  </Link>
                  <Link :href="route('settings.index')"
                        class="block px-4 py-2 text-sm text-gray-300 hover:text-white transition"
                        style="text-decoration:none;"
                        @click="dropdownOpen = false">
                    Settings
                  </Link>
                  <Link v-if="user.rank >= 1"
                        :href="route('admin.index')"
                        class="block px-4 py-2 text-sm font-semibold transition rank-admin"
                        style="text-decoration:none;"
                        @click="dropdownOpen = false">
                    Admin Panel
                  </Link>
                  <div style="border-top:1px solid #383838; margin:0.25rem 0;"></div>
                  <Link :href="route('logout')" method="post" as="button"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-400 hover:text-white transition"
                        @click="dropdownOpen = false">
                    Logout
                  </Link>
                </div>
              </div>
            </template>

            <template v-else>
              <Link :href="route('login')"
                    class="px-4 py-1.5 text-sm font-medium text-gray-300 hover:text-white transition"
                    style="text-decoration:none;">
                Login
              </Link>
              <Link :href="route('register')"
                    class="px-4 py-1.5 text-sm font-semibold text-white btn-flat transition"
                    style="background:#1b6182; text-decoration:none;">
                Register
              </Link>
            </template>

            <!-- Mobile toggle -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-400 hover:text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile menu (mirrors original sidebar-nav style) -->
        <div v-if="mobileOpen" class="md:hidden pb-3 sidebar-nav">
          <ul>
            <li><Link :href="route('home')" @click="mobileOpen=false">Home</Link></li>
            <li><Link :href="route('catalog.index')" @click="mobileOpen=false">Catalog</Link></li>
            <li><Link :href="route('forum.index')" @click="mobileOpen=false">Forum</Link></li>
            <li><Link :href="route('users.index')" @click="mobileOpen=false">Users</Link></li>
            <template v-if="user">
              <li><Link :href="route('friends.index')" @click="mobileOpen=false">Friends</Link></li>
              <li><Link :href="route('messages.index')" @click="mobileOpen=false">Messages</Link></li>
              <li><Link :href="route('settings.index')" @click="mobileOpen=false">Settings</Link></li>
            </template>
          </ul>
        </div>
      </div>
    </nav>

    <!-- ── Flash messages ──────────────────────────────────────────────── -->
    <div v-if="flash && (flash.success || flash.error)" class="max-w-7xl mx-auto px-4 pt-3">
      <div v-if="flash.success"
           class="flex items-center gap-3 px-4 py-3 text-sm"
           style="background:rgba(14,117,41,0.2); border:1px solid #0E7529; color:#4ade80; border-radius:2px;">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ flash.success }}
      </div>
      <div v-if="flash.error"
           class="flex items-center gap-3 px-4 py-3 text-sm"
           style="background:rgba(192,57,43,0.2); border:1px solid #c0392b; color:#f87171; border-radius:2px;">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ flash.error }}
      </div>
    </div>

    <!-- ── Page content ────────────────────────────────────────────────── -->
    <main class="max-w-7xl mx-auto px-4 py-6">
      <slot />
    </main>

    <!-- ── Footer (original footer adapted for dark theme) ─────────────── -->
    <footer class="gt-footer mt-16 py-6">
      <div class="max-w-7xl mx-auto px-4 text-center text-sm">
        <p>&copy; {{ new Date().getFullYear() }} Graphictoria &mdash; All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
