<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-100">Settings</h1>

      <!-- About -->
      <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 text-indigo-400">About Me</h2>
        <form @submit.prevent="submitAbout">
          <textarea
            v-model="aboutForm.about"
            rows="5"
            placeholder="Write something about yourself…"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="aboutForm.errors.about" class="text-red-400 text-xs mt-1">{{ aboutForm.errors.about }}</p>
          <button
            type="submit"
            :disabled="aboutForm.processing"
            class="mt-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-6 py-2 rounded font-semibold"
          >
            {{ aboutForm.processing ? 'Saving…' : 'Save' }}
          </button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 text-indigo-400">Change Password</h2>
        <form @submit.prevent="submitPassword" class="space-y-4">
          <div>
            <label class="block text-sm text-gray-400 mb-1">Current Password</label>
            <input
              v-model="passwordForm.current_password"
              type="password"
              class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
            />
            <p v-if="passwordForm.errors.current_password" class="text-red-400 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
          </div>
          <div>
            <label class="block text-sm text-gray-400 mb-1">New Password</label>
            <input
              v-model="passwordForm.password"
              type="password"
              class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
            />
            <p v-if="passwordForm.errors.password" class="text-red-400 text-xs mt-1">{{ passwordForm.errors.password }}</p>
          </div>
          <div>
            <label class="block text-sm text-gray-400 mb-1">Confirm New Password</label>
            <input
              v-model="passwordForm.password_confirmation"
              type="password"
              class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
            />
            <p v-if="passwordForm.errors.password_confirmation" class="text-red-400 text-xs mt-1">{{ passwordForm.errors.password_confirmation }}</p>
          </div>
          <button
            type="submit"
            :disabled="passwordForm.processing"
            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-6 py-2 rounded font-semibold"
          >
            {{ passwordForm.processing ? 'Updating…' : 'Update Password' }}
          </button>
        </form>
      </div>

      <!-- 2FA -->
      <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 text-indigo-400">Two-Factor Authentication</h2>
        <div v-if="user.two_factor_enabled">
          <p class="text-green-400 text-sm mb-4">2FA is currently <strong>enabled</strong>.</p>
          <form @submit.prevent="disableTwoFactor">
            <div class="mb-3">
              <label class="block text-sm text-gray-400 mb-1">Confirm your password to disable 2FA</label>
              <input
                v-model="twoFactorForm.password"
                type="password"
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
              />
              <p v-if="twoFactorForm.errors.password" class="text-red-400 text-xs mt-1">{{ twoFactorForm.errors.password }}</p>
            </div>
            <button
              type="submit"
              :disabled="twoFactorForm.processing"
              class="bg-red-700 hover:bg-red-600 disabled:opacity-50 px-6 py-2 rounded font-semibold"
            >
              Disable 2FA
            </button>
          </form>
        </div>
        <div v-else>
          <p class="text-gray-400 text-sm mb-4">2FA is currently <strong class="text-gray-300">disabled</strong>. Enable it for extra security.</p>
          <form @submit.prevent="enableTwoFactor">
            <div class="mb-3">
              <label class="block text-sm text-gray-400 mb-1">Confirm your password to enable 2FA</label>
              <input
                v-model="twoFactorForm.password"
                type="password"
                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
              />
              <p v-if="twoFactorForm.errors.password" class="text-red-400 text-xs mt-1">{{ twoFactorForm.errors.password }}</p>
            </div>
            <button
              type="submit"
              :disabled="twoFactorForm.processing"
              class="bg-green-700 hover:bg-green-600 disabled:opacity-50 px-6 py-2 rounded font-semibold"
            >
              Enable 2FA
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
})

const aboutForm = useForm({ about: props.user.about ?? '' })
function submitAbout() {
  aboutForm.patch(route('settings.about'))
}

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})
function submitPassword() {
  passwordForm.patch(route('settings.password'), {
    onSuccess: () => passwordForm.reset(),
  })
}

const twoFactorForm = useForm({ password: '' })
function enableTwoFactor() {
  twoFactorForm.post(route('two-factor.enable'), { onSuccess: () => twoFactorForm.reset() })
}
function disableTwoFactor() {
  twoFactorForm.delete(route('two-factor.disable'), { onSuccess: () => twoFactorForm.reset() })
}
</script>
