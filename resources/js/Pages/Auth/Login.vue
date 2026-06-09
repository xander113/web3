<template>
  <AppLayout>
    <div class="max-w-md mx-auto mt-12">
      <h1 class="text-2xl font-bold mb-6 text-center text-indigo-400">Login</h1>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-8 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Username</label>
          <input
            v-model="form.username"
            type="text"
            autocomplete="username"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.username" class="text-red-400 text-xs mt-1">{{ form.errors.username }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</p>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="form.remember" type="checkbox" id="remember" class="accent-indigo-500" />
          <label for="remember" class="text-sm text-gray-400">Remember me</label>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Logging in…' : 'Login' }}
        </button>
        <p class="text-center text-sm text-gray-500">
          Don't have an account?
          <Link href="/register" class="text-indigo-400 hover:underline">Register</Link>
        </p>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({
  username: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>
