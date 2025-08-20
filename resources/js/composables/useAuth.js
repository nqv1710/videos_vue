import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import axios from 'axios'

export default function useAuth() {
  const page = usePage()
  const store = useStore()

  const user = computed(() => page.props.auth?.user ?? store.getters['auth/user'])
  const token = computed(() => store.getters['auth/token'])

  const ensureUserLoaded = async () => {
    if (!user.value && token.value) {
      try {
        await store.dispatch('auth/getUser')
      } catch (e) {
        // ignore
      }
    }
    return user.value
  }

  return { user, token, ensureUserLoaded }
}
