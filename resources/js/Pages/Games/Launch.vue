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

        <!-- Launch status -->
        <div v-if="launched" class="space-y-4">
          <div class="flex items-center justify-center gap-3 text-green-400">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-semibold">
              {{ isStudio ? 'Studio is launching…' : 'Client is launching…' }}
            </span>
          </div>
          <p class="text-sm text-gray-500">
            If the {{ isStudio ? 'Studio' : 'client' }} does not open automatically,
            make sure Graphictoria is installed and try the button below.
          </p>
        </div>

        <div v-else class="space-y-4">
          <p class="text-gray-400 text-sm">
            Clicking Launch will open the Graphictoria
            {{ isStudio ? 'Studio' : 'Player' }}.
            You must have it installed on your computer.
          </p>
        </div>

        <!-- Launch button -->
        <div class="mt-6 space-y-3">
          <button
            @click="doLaunch"
            class="btn-flat px-8 py-3 text-white font-semibold text-base"
            style="background:#1b6182;"
          >
            {{ launched ? 'Re-launch' : (isStudio ? 'Open in Studio' : 'Play Now') }}
          </button>

          <!-- Download link if client not installed -->
          <div class="mt-4">
            <p class="text-xs text-gray-600">
              Don't have it installed?
              <a :href="downloadUrl" class="text-[#17BCCF] hover:underline" target="_blank">
                Download Graphictoria {{ isStudio ? 'Studio' : 'Player' }}
              </a>
            </p>
          </div>
        </div>

        <!-- Install instructions -->
        <div class="mt-8 text-left text-xs text-gray-600 space-y-1 border-t border-gray-700 pt-4">
          <p class="font-semibold text-gray-500 mb-2">First time?</p>
          <p>1. Download the Graphictoria client from the link above.</p>
          <p>2. Run the installer (or extract the zip) on your computer.</p>
          <p>3. Come back here and click Play Now — your browser will open the game automatically.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  server:     { type: Object, required: true },
  launchUri:  { type: String, required: true },
  clientPath: { type: String, default: '' },
  isStudio:   { type: Boolean, default: false },
})

const launched = ref(false)
const downloadUrl = '/downloads/graphictoria-' + (props.isStudio ? 'studio' : 'player')

function doLaunch() {
  launched.value = true
  // Use an anchor element click — window.location.href throws DOMException for custom URI schemes
  const a = document.createElement('a')
  a.href = props.launchUri
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}
</script>
