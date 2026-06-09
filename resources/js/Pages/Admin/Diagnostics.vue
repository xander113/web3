<template>
  <AppLayout>
    <h1 class="text-2xl font-bold mb-2">Diagnostics</h1>
    <p class="text-gray-500 text-sm mb-6">Test Cloud Compute rendering, game join flows, and server connectivity. All calls are made server-side.</p>

    <!-- Config status -->
    <div class="gt-panel mb-6">
      <h2 class="font-semibold mb-3" style="color:#17BCCF;">Environment Configuration</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
        <div class="rounded p-3" style="background:#1a1a1a; border:1px solid #333;">
          <p class="text-gray-500 text-xs mb-1">CLOUD_COMPUTE_URL</p>
          <p :class="cloudComputeUrl ? 'text-green-400' : 'text-red-400'" class="font-mono text-xs break-all">
            {{ cloudComputeUrl || 'Not set' }}
          </p>
        </div>
        <div class="rounded p-3" style="background:#1a1a1a; border:1px solid #333;">
          <p class="text-gray-500 text-xs mb-1">G5_CLIENT_PATH</p>
          <p :class="clientPath ? 'text-green-400' : 'text-yellow-500'" class="font-mono text-xs break-all">
            {{ clientPath || 'Not set (users install client)' }}
          </p>
        </div>
        <div class="rounded p-3" style="background:#1a1a1a; border:1px solid #333;">
          <p class="text-gray-500 text-xs mb-1">G5_STUDIO_PATH</p>
          <p :class="studioPath ? 'text-green-400' : 'text-yellow-500'" class="font-mono text-xs break-all">
            {{ studioPath || 'Not set' }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Cloud Compute Ping -->
      <div class="gt-panel">
        <h2 class="font-semibold mb-3" style="color:#17BCCF;">Cloud Compute — Ping</h2>
        <p class="text-gray-500 text-xs mb-3">Checks if GtoriaCompute is reachable at the configured URL.</p>
        <button @click="ping" :disabled="loading.ping" class="btn-flat px-4 py-2 text-white text-sm mb-3" style="background:#1b6182;">
          {{ loading.ping ? 'Pinging…' : 'Ping Service' }}
        </button>
        <ResultBox :result="results.ping" />
      </div>

      <!-- Avatar Render -->
      <div class="gt-panel">
        <h2 class="font-semibold mb-3" style="color:#17BCCF;">Render Avatar</h2>
        <p class="text-gray-500 text-xs mb-3">Request an avatar thumbnail from Cloud Compute for a given user ID.</p>
        <div class="flex gap-2 mb-3">
          <input v-model.number="inputs.userId" type="number" placeholder="User ID" min="1"
                 class="flex-1 px-3 py-2 text-sm" style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
          <button @click="renderAvatar" :disabled="loading.avatar || !inputs.userId" class="btn-flat px-4 py-2 text-white text-sm" style="background:#1b6182;">
            {{ loading.avatar ? 'Rendering…' : 'Render' }}
          </button>
        </div>
        <ResultBox :result="results.avatar" />
        <img v-if="results.avatar?.ok && results.avatar?.thumbUrl" :src="results.avatar.thumbUrl" class="mt-3 w-24 h-24 object-cover" style="border:1px solid #444;" />
      </div>

      <!-- Catalog Item Render -->
      <div class="gt-panel">
        <h2 class="font-semibold mb-3" style="color:#17BCCF;">Render Catalog Item</h2>
        <p class="text-gray-500 text-xs mb-3">Request a thumbnail render for a catalog item (must have a .rbxm model file).</p>
        <div class="flex gap-2 mb-3">
          <input v-model.number="inputs.itemId" type="number" placeholder="Item ID" min="1"
                 class="flex-1 px-3 py-2 text-sm" style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
          <button @click="renderItem" :disabled="loading.item || !inputs.itemId" class="btn-flat px-4 py-2 text-white text-sm" style="background:#1b6182;">
            {{ loading.item ? 'Rendering…' : 'Render' }}
          </button>
        </div>
        <ResultBox :result="results.item" />
        <img v-if="results.item?.ok && results.item?.thumbUrl" :src="results.item.thumbUrl" class="mt-3 w-24 h-24 object-cover" style="border:1px solid #444;" />
      </div>

      <!-- Game Join Test -->
      <div class="gt-panel">
        <h2 class="font-semibold mb-3" style="color:#17BCCF;">Test Game Join Flow</h2>
        <p class="text-gray-500 text-xs mb-3">Simulates the full auth-ticket → PlaceLauncher → Join.ashx flow for a server.</p>
        <div class="flex gap-2 mb-3">
          <input v-model.number="inputs.serverId" type="number" placeholder="Server ID" min="1"
                 class="flex-1 px-3 py-2 text-sm" style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
          <button @click="testJoin" :disabled="loading.join || !inputs.serverId" class="btn-flat px-4 py-2 text-white text-sm" style="background:#1b6182;">
            {{ loading.join ? 'Testing…' : 'Test Join' }}
          </button>
        </div>
        <ResultBox :result="results.join" />
        <!-- Join log steps -->
        <div v-if="results.join?.log" class="mt-3 space-y-1">
          <div v-for="(step, i) in results.join.log" :key="i"
               class="flex items-start gap-2 text-xs rounded px-2 py-1"
               :style="step.ok === false ? 'background:rgba(192,57,43,0.15)' : step.ok ? 'background:rgba(14,117,41,0.15)' : 'background:#222'">
            <span :class="step.ok === false ? 'text-red-400' : step.ok ? 'text-green-400' : 'text-gray-500'" class="font-mono shrink-0">
              {{ step.ok === false ? '✗' : step.ok ? '✓' : '?' }}
            </span>
            <span class="text-gray-400 shrink-0">{{ step.step }}</span>
            <span class="text-gray-600 break-all">{{ step.data || step.body || step.error || '' }}</span>
            <span v-if="step.ms" class="ml-auto shrink-0 text-gray-700">{{ step.ms }}ms</span>
          </div>
        </div>
      </div>

      <!-- Stress Test -->
      <div class="gt-panel lg:col-span-2">
        <h2 class="font-semibold mb-3" style="color:#17BCCF;">Stress Test — Bulk Rendering</h2>
        <p class="text-gray-500 text-xs mb-3">
          Renders N avatars or catalog items sequentially, measuring success rate and timing.
          Max 20 per run.
        </p>
        <div class="flex gap-3 flex-wrap mb-3">
          <select v-model="inputs.stressType" class="px-3 py-2 text-sm" style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;">
            <option value="avatar">Avatars</option>
            <option value="item">Catalog Items</option>
          </select>
          <input v-model.number="inputs.stressCount" type="number" min="1" max="20" placeholder="Count (max 20)"
                 class="w-36 px-3 py-2 text-sm" style="background:#1a1a1a; border:1px solid #444; color:#e8e8e8;" />
          <button @click="stressTest" :disabled="loading.stress" class="btn-flat px-4 py-2 text-white text-sm" style="background:#1b6182;">
            {{ loading.stress ? 'Running…' : 'Run Stress Test' }}
          </button>
        </div>

        <ResultBox :result="results.stress" />

        <div v-if="results.stress?.results" class="mt-3">
          <div class="flex gap-4 text-sm mb-3">
            <span class="text-green-400">✓ {{ results.stress.succeeded }} succeeded</span>
            <span class="text-red-400">✗ {{ results.stress.failed }} failed</span>
            <span class="text-gray-500">Total: {{ results.stress.total_ms }}ms</span>
            <span class="text-gray-500">Avg: {{ results.stress.count ? Math.round(results.stress.total_ms / results.stress.count) : 0 }}ms/render</span>
          </div>
          <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-10 gap-1">
            <div v-for="r in results.stress.results" :key="r.id"
                 class="rounded text-center py-1 text-xs"
                 :style="r.ok ? 'background:rgba(14,117,41,0.3); color:#4ade80;' : 'background:rgba(192,57,43,0.3); color:#f87171;'">
              <div>{{ r.ok ? '✓' : '✗' }}</div>
              <div class="text-gray-600">#{{ r.id }}</div>
              <div>{{ r.ms }}ms</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import AppLayout from '@/Components/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  cloudComputeUrl: { type: String, default: '' },
  clientPath:      { type: String, default: '' },
  studioPath:      { type: String, default: '' },
})

const inputs = reactive({
  userId:      null,
  itemId:      null,
  serverId:    null,
  stressType:  'avatar',
  stressCount: 5,
})

const loading = reactive({ ping: false, avatar: false, item: false, join: false, stress: false })
const results = reactive({ ping: null, avatar: null, item: null, join: null, stress: null })

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? ''

async function post(url, data) {
  return axios.post(url, data, { headers: { 'X-CSRF-TOKEN': csrf() } }).then(r => r.data)
}

async function ping() {
  loading.ping = true
  results.ping = null
  results.ping = await post(route('admin.diagnostics.ping'), {}).catch(e => ({ ok: false, error: e.message }))
  loading.ping = false
}

async function renderAvatar() {
  loading.avatar = true
  results.avatar = null
  results.avatar = await post(route('admin.diagnostics.render_avatar'), { user_id: inputs.userId }).catch(e => ({ ok: false, error: e.response?.data?.message ?? e.message }))
  loading.avatar = false
}

async function renderItem() {
  loading.item = true
  results.item = null
  results.item = await post(route('admin.diagnostics.render_item'), { item_id: inputs.itemId }).catch(e => ({ ok: false, error: e.response?.data?.message ?? e.message }))
  loading.item = false
}

async function testJoin() {
  loading.join = true
  results.join = null
  results.join = await post(route('admin.diagnostics.test_join'), { server_id: inputs.serverId }).catch(e => ({ ok: false, error: e.response?.data?.message ?? e.message }))
  loading.join = false
}

async function stressTest() {
  loading.stress = true
  results.stress = null
  results.stress = await post(route('admin.diagnostics.stress'), { count: inputs.stressCount, type: inputs.stressType }).catch(e => ({ ok: false, error: e.response?.data?.message ?? e.message }))
  loading.stress = false
}

// Simple result display component defined inline
const ResultBox = {
  props: { result: { default: null } },
  template: `
    <div v-if="result !== null" class="rounded px-3 py-2 text-xs font-mono"
         :style="result?.ok ? 'background:rgba(14,117,41,0.2); border:1px solid #0E7529; color:#4ade80;' : 'background:rgba(192,57,43,0.2); border:1px solid #c0392b; color:#f87171;'">
      <span v-if="result?.ok">✓ OK{{ result.ms ? (' — ' + result.ms + 'ms') : '' }}</span>
      <span v-else>✗ {{ result?.error || ('HTTP ' + result?.status) }}</span>
      <span v-if="result?.body" class="block text-gray-600 mt-1 whitespace-pre-wrap break-all">{{ result.body }}</span>
    </div>
  `,
}
</script>
