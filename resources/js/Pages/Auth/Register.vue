<template>
  <AppLayout>
    <div class="max-w-md mx-auto mt-12">
      <h1 class="text-2xl font-bold mb-6 text-center text-indigo-400">Create Account</h1>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-8 space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Username</label>
          <input
            v-model="form.username"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.username" class="text-red-400 text-xs mt-1">{{ form.errors.username }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.password_confirmation" class="text-red-400 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Creating account…' : 'Register' }}
        </button>
        <p class="text-center text-sm text-gray-500">
          Already have an account?
          <Link href="/login" class="text-indigo-400 hover:underline">Login</Link>
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
  email: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
