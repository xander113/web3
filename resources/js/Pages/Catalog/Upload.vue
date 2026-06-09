<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <Link href="/catalog" class="text-[#17BCCF] hover:underline text-sm">&larr; Catalog</Link>
      <h1 class="text-2xl font-bold mt-4 mb-6">Upload Item</h1>
      <div class="px-4 py-3 text-sm mb-6" style="background:rgba(180,130,0,0.15); border:1px solid #b08000; color:#f0c040; border-radius:2px;">
        Uploaded items are pending review before they appear in the catalog.
        3D items (hats, heads, gear, etc.) require a <strong>.rbxm</strong> model file.
        A thumbnail image (.png) is recommended so your item displays correctly.
      </div>
      <form @submit.prevent="submit" class="gt-panel space-y-5">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none"
            style="border-radius:2px;"
          />
          <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Type</label>
          <select
            v-model="form.type"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none"
            style="border-radius:2px;"
          >
            <option value="">Select a type</option>
            <option value="hat">Hat (3D)</option>
            <option value="head">Head (3D)</option>
            <option value="face">Face (2D)</option>
            <option value="shirt">Shirt (2D)</option>
            <option value="pants">Pants (2D)</option>
            <option value="tshirt">T-Shirt (2D)</option>
            <option value="gear">Gear (3D)</option>
            <option value="decal">Decal (2D)</option>
          </select>
          <p v-if="form.errors.type" class="text-red-400 text-xs mt-1">{{ form.errors.type }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none resize-none"
            style="border-radius:2px;"
          ></textarea>
          <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Price (coins &mdash; 0 = free)</label>
          <input
            v-model.number="form.price"
            type="number"
            min="0"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none"
            style="border-radius:2px;"
          />
          <p v-if="form.errors.price" class="text-red-400 text-xs mt-1">{{ form.errors.price }}</p>
        </div>

        <!-- Model file — 3D item types -->
        <div v-if="is3D">
          <label class="block text-sm text-gray-400 mb-1">
            Model File <span class="text-gray-500">(.rbxm or .rbxmx)</span>
          </label>
          <input
            type="file"
            accept=".rbxm,.rbxmx"
            @change="handleModel"
            class="w-full text-gray-400 text-sm"
          />
          <p v-if="form.errors.model_file" class="text-red-400 text-xs mt-1">{{ form.errors.model_file }}</p>
        </div>

        <!-- Thumbnail image -->
        <div>
          <label class="block text-sm text-gray-400 mb-1">
            Thumbnail Image <span class="text-gray-500">(.png, .jpg &mdash; recommended)</span>
          </label>
          <input
            type="file"
            accept="image/png,image/jpeg"
            @change="handleImage"
            class="w-full text-gray-400 text-sm"
          />
          <p v-if="form.errors.image_file" class="text-red-400 text-xs mt-1">{{ form.errors.image_file }}</p>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="btn-flat w-full py-2 text-white font-semibold"
          style="background:#1b6182;"
        >
          {{ form.processing ? 'Uploading…' : 'Submit for Review' }}
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const form = useForm({
  name:        '',
  type:        '',
  description: '',
  price:       0,
  model_file:  null,
  image_file:  null,
})

const threeDTypes = ['hat', 'head', 'gear']
const is3D = computed(() => threeDTypes.includes(form.type))

function handleModel(e) { form.model_file = e.target.files[0] ?? null }
function handleImage(e)  { form.image_file = e.target.files[0] ?? null }

function submit() {
  form.post(route('catalog.store'), { forceFormData: true })
}
</script>
