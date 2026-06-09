<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto space-y-4">
      <!-- Breadcrumb -->
      <div class="text-sm text-gray-500">
        <Link href="/forum" class="text-indigo-400 hover:underline">Forum</Link>
        &rsaquo;
        <Link :href="route('forum.show', topic.forum_id)" class="text-indigo-400 hover:underline">{{ topic.forum?.name }}</Link>
        &rsaquo;
        <span class="text-gray-300">{{ topic.title }}</span>
      </div>

      <h1 class="text-2xl font-bold text-gray-100">{{ topic.title }}</h1>
      <div v-if="topic.is_locked" class="bg-red-900/40 border border-red-700 text-red-300 rounded px-4 py-2 text-sm">
        This topic is locked.
      </div>

      <!-- Original post -->
      <div class="bg-gray-800 rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
          <Link :href="route('profile.show', topic.user?.id)" class="flex items-center gap-2 hover:opacity-80">
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold">
              {{ (topic.user?.username ?? '?')[0].toUpperCase() }}
            </div>
            <span class="text-gray-200 font-medium text-sm">{{ topic.user?.username }}</span>
          </Link>
          <span class="text-xs text-gray-500">{{ formatDate(topic.created_at) }}</span>
        </div>
        <div class="text-gray-300 whitespace-pre-line leading-relaxed">{{ topic.body }}</div>
        <form v-if="canDelete(topic)" @submit.prevent="deletePost('topic', topic.id)" class="mt-3 text-right">
          <button class="text-red-400 hover:text-red-300 text-xs">Delete</button>
        </form>
      </div>

      <!-- Replies -->
      <div v-for="reply in replyList" :key="reply.id" class="bg-gray-800 rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
          <Link :href="route('profile.show', reply.user?.id)" class="flex items-center gap-2 hover:opacity-80">
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold">
              {{ (reply.user?.username ?? '?')[0].toUpperCase() }}
            </div>
            <span class="text-gray-200 font-medium text-sm">{{ reply.user?.username }}</span>
          </Link>
          <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
        </div>
        <div class="text-gray-300 whitespace-pre-line leading-relaxed">{{ reply.body }}</div>
        <form v-if="canDelete(reply)" @submit.prevent="deletePost('reply', reply.id)" class="mt-3 text-right">
          <button class="text-red-400 hover:text-red-300 text-xs">Delete</button>
        </form>
      </div>

      <!-- Pagination -->
      <div v-if="replies.links" class="flex justify-center gap-2">
        <Link
          v-for="link in replies.links"
          :key="link.label"
          :href="link.url ?? '#'"
          :class="[
            'px-3 py-1 rounded text-sm',
            link.active ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
            !link.url ? 'opacity-40 pointer-events-none' : '',
          ]"
          v-html="link.label"
        />
      </div>

      <!-- Reply form -->
      <div v-if="auth.user && !topic.is_locked" class="bg-gray-800 rounded-xl p-5">
        <h3 class="text-base font-semibold mb-3 text-gray-200">Post a Reply</h3>
        <form @submit.prevent="submitReply">
          <textarea
            v-model="replyForm.body"
            rows="5"
            placeholder="Write your reply…"
            class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-indigo-500 resize-none"
          ></textarea>
          <p v-if="replyForm.errors.body" class="text-red-400 text-xs mt-1">{{ replyForm.errors.body }}</p>
          <button
            type="submit"
            :disabled="replyForm.processing"
            class="mt-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-6 py-2 rounded font-semibold text-sm"
          >
            {{ replyForm.processing ? 'Posting…' : 'Post Reply' }}
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/AppLayout.vue'

const props = defineProps({
  topic: { type: Object, required: true },
  replies: { type: Object, required: true },
})

const page = usePage()
const auth = computed(() => page.props.auth ?? { user: null })
const replyList = ref([...(props.replies.data ?? [])])

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}

function canDelete(post) {
  if (!auth.value.user) return false
  // rank 1 = admin, rank 2 = moderator — both rank >= 1 can moderate
  return auth.value.user.id === post.user_id || auth.value.user.rank >= 1
}

const replyForm = useForm({ body: '' })
function submitReply() {
  replyForm.post(route('forum.topic.reply', props.topic.id), {
    onSuccess: () => replyForm.reset(),
  })
}

const deleteForm = useForm({})
function deletePost(type, id) {
  if (!confirm('Delete this post?')) return
  const routeName = type === 'topic' ? 'forum.topic.delete' : 'forum.reply.delete'
  deleteForm.delete(route(routeName, id))
}

let topicChannel = null
onMounted(() => {
  if (!window.Echo) return
  topicChannel = window.Echo.channel(`topic.${props.topic.id}`)
  // broadcastWith() returns {id, body, user, created_at} directly (not wrapped in .reply)
  topicChannel.listen('ForumReplyPosted', (e) => {
    if (!replyList.value.find((r) => r.id === e.id)) {
      replyList.value.push(e)
    }
  })
})
onUnmounted(() => {
  if (topicChannel) window.Echo.leaveChannel(`topic.${props.topic.id}`)
})
</script>
