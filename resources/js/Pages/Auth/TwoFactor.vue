<template>
  <AppLayout>
    <div class="max-w-sm mx-auto mt-16">
      <h1 class="text-2xl font-bold mb-2 text-center text-indigo-400">Two-Factor Authentication</h1>
      <p class="text-gray-400 text-center text-sm mb-6">Enter the 6-digit code from your authenticator app.</p>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-8 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Authentication Code</label>
          <input
            v-model="form.code"
            type="text"
            inputmode="numeric"
            maxlength="6"
            placeholder="000000"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 text-center text-2xl tracking-widest focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.code" class="text-red-400 text-xs mt-1">{{ form.errors.code }}</p>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Verifying…' : 'Verify' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({ code: '' })

function submit() {
  form.post(route('two-factor.verify'))
}
</script>
