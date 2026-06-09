<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <Link href="/catalog" class="text-indigo-400 hover:underline text-sm">&larr; Catalog</Link>

      <div class="bg-gray-800 rounded-xl p-6 mt-4 flex gap-6 flex-col sm:flex-row">
        <!-- Image -->
        <div class="w-full sm:w-48 shrink-0">
          <div class="aspect-square bg-gray-700 rounded-lg flex items-center justify-center text-gray-500">
            <span v-if="!item.image_url">No image</span>
            <img v-else :src="item.image_url" :alt="item.name" class="w-full h-full object-cover rounded-lg" />
          </div>
        </div>

        <!-- Details -->
        <div class="flex-1 space-y-3">
          <h1 class="text-2xl font-bold text-gray-100">{{ item.name }}</h1>
          <p class="text-sm text-gray-400 capitalize">Type: {{ item.type }}</p>
          <p class="text-sm text-gray-400">
            By:
            <Link :href="route('profile.show', item.creator?.username)" class="text-indigo-400 hover:underline">
              {{ item.creator?.username ?? 'Graphictoria' }}
            </Link>
          </p>
          <p v-if="item.description" class="text-gray-300 text-sm leading-relaxed">{{ item.description }}</p>

          <div class="pt-2">
            <p class="text-2xl font-bold text-yellow-400 mb-3">
              {{ item.price === 0 ? 'Free' : `${item.price} coins` }}
            </p>
            <span v-if="owned" class="inline-block bg-green-800 text-green-300 px-4 py-2 rounded font-semibold text-sm">
              Owned
            </span>
            <form v-else-if="auth.user" @submit.prevent="buyItem">
              <button
                type="submit"
                :disabled="buyForm.processing"
                class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-6 py-2 rounded font-semibold"
              >
                {{ buyForm.processing ? 'Buying…' : (item.price === 0 ? 'Get for Free' : 'Buy Now') }}
              </button>
            </form>
            <Link v-else href="/login" class="inline-block bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded font-semibold text-sm">
              Login to Buy
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  item: { type: Object, required: true },
  owned: { type: Boolean, default: false },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })

const buyForm = useForm({})
function buyItem() {
  buyForm.post(route('catalog.buy', props.item.id))
}
</script>
