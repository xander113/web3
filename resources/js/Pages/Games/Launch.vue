<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto mt-12 text-center space-y-6">
      <div class="gt-panel p-8">
        <h1 class="text-2xl font-bold mb-2">
          {{ isStudio ? 'Opening Studio' : 'Launching Game' }}
        </h1>
        <p class="text-gray-400 mb-6">
          <span class="font-semibold text-gray-200">{{ server.name }}</span>
        </p>

        <!-- Status -->
        <div v-if="launched" class="flex items-center justify-center gap-3 mb-6 text-green-400">
          <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="font-semibold">{{ isStudio ? 'Studio is launching…' : 'Client is launching…' }}</span>
        </div>
        <div v-else class="mb-6">
          <p class="text-gray-400 text-sm">
            Clicking Launch will open the Graphictoria {{ isStudio ? 'Studio' : 'Player' }}.
            You must have it installed on your computer.
          </p>
        </div>

        <!-- Error -->
        <div v-if="launchError" class="mb-4 rounded px-3 py-2 text-sm text-red-300" style="background:rgba(192,57,43,0.2); border:1px solid #c0392b;">
          {{ launchError }}
        </div>

        <!-- Buttons -->
        <div class="mt-4 space-y-3">
          <!-- Dev-launch: when G5 path is configured, call the server to exec the exe directly -->
          <template v-if="clientPath">
            <button
              @click="doDevLaunch"
              :disabled="devLaunching"
              class="btn-flat px-8 py-3 text-white font-semibold text-base w-full"
              style="background:#1b6182;"
            >
              {{ devLaunching ? 'Launching…' : (launched ? 'Re-launch' : (isStudio ? 'Open in Studio' : 'Play Now')) }}
            </button>
            <p class="text-xs text-gray-600">
              Dev mode — launching from <span class="font-mono text-gray-500">{{ clientPath }}</span>
            </p>
          </template>

          <!-- URI scheme launch: when no local path (production / URI handler registered) -->
          <template v-else>
            <button
              @click="doUriLaunch"
              class="btn-flat px-8 py-3 text-white font-semibold text-base w-full"
              style="background:#1b6182;"
            >
              {{ launched ? 'Re-launch' : (isStudio ? 'Open in Studio' : 'Play Now') }}
            </button>
            <p class="text-xs text-gray-600">
              Don't have it installed?
              <a :href="downloadUrl" class="text-[#17BCCF] hover:underline" target="_blank">
                Download Graphictoria {{ isStudio ? 'Studio' : 'Player' }}
              </a>
            </p>
          </template>
        </div>

        <!-- First-time instructions -->
        <div class="mt-8 text-left text-xs text-gray-600 space-y-1 border-t border-gray-700 pt-4">
          <p class="font-semibold text-gray-500 mb-2">First time?</p>
          <p>1. Download the Graphictoria client from the link above.</p>
          <p>2. Run the installer on your computer.</p>
          <p>3. Come back here and click Play Now — your browser will open the game automatically.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  server:     { type: Object, required: true },
  launchUri:  { type: String, required: true },
  clientPath: { type: String, default: '' },
  isStudio:   { type: Boolean, default: false },
})

const launched     = ref(false)
const devLaunching = ref(false)
const launchError  = ref('')
const downloadUrl  = '/downloads/graphictoria-' + (props.isStudio ? 'studio' : 'player')

async function doDevLaunch() {
  devLaunching.value = true
  launchError.value  = ''
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
    const res  = await axios.post(
      route('games.dev_launch', props.server.id),
      { type: props.isStudio ? 'studio' : 'player' },
      { headers: { 'X-CSRF-TOKEN': csrf } }
    )
    if (res.data.ok) {
      launched.value = true
    } else {
      launchError.value = res.data.error ?? 'Launch failed.'
    }
  } catch (e) {
    launchError.value = e.response?.data?.message ?? e.message
  } finally {
    devLaunching.value = false
  }
}

function doUriLaunch() {
  launched.value = true
  // Must use anchor click — window.location.href throws DOMException for custom URI schemes
  const a = document.createElement('a')
  a.href = props.launchUri
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}
</script>
