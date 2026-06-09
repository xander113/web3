<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <Link href="/catalog" class="text-indigo-400 hover:underline text-sm">&larr; Catalog</Link>
      <h1 class="text-2xl font-bold mt-4 mb-6 text-gray-100">Upload Item</h1>
      <div class="bg-yellow-900/40 border border-yellow-700 text-yellow-300 rounded px-4 py-3 text-sm mb-6">
        Uploaded items are pending review before they appear in the catalog.
      </div>
      <form @submit.prevent="submit" class="bg-gray-800 rounded-xl p-6 space-y-5" enctype="multipart/form-data">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Type</label>
          <select
            v-model="form.type"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          >
            <option value="">Select a type</option>
            <option value="hat">Hat</option>
            <option value="head">Head</option>
            <option value="face">Face</option>
            <option value="shirt">Shirt</option>
            <option value="pants">Pants</option>
            <option value="tshirt">T-Shirt</option>
            <option value="gear">Gear</option>
            <option value="decal">Decal</option>
          </select>
          <p v-if="form.errors.type" class="text-red-400 text-xs mt-1">{{ form.errors.type }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Price (coins, 0 = free)</label>
          <input
            v-model.number="form.price"
            type="number"
            min="0"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500"
          />
          <p v-if="form.errors.price" class="text-red-400 text-xs mt-1">{{ form.errors.price }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Image File</label>
          <input
            type="file"
            accept="image/*"
            @change="handleFile"
            class="w-full text-gray-400 text-sm"
          />
          <p v-if="form.errors.image" class="text-red-400 text-xs mt-1">{{ form.errors.image }}</p>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2 rounded font-semibold"
        >
          {{ form.processing ? 'Uploading…' : 'Submit for Review' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({
  name: '',
  type: '',
  description: '',
  price: 0,
  image: null,
})

function handleFile(e) {
  form.image = e.target.files[0] ?? null
}

function submit() {
  form.post(route('catalog.store'))
}
</script>
