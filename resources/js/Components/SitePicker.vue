<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    site:      { type: Object, required: true },
    routeBase: { type: String, required: true },
})

const page     = usePage()
const allSites = computed(() => page.props.allSites ?? [])
const open     = ref(false)
const wrapper  = ref(null)

const label = computed(() => props.site.domain || props.site.name)

const handleOutside = (e) => {
    if (wrapper.value && !wrapper.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', handleOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleOutside))
</script>

<template>
    <div ref="wrapper" class="relative">
        <button
            @click.stop="open = !open"
            class="flex items-center gap-1.5 border border-gray-200 rounded-full px-3 py-1.5 text-gray-800 font-semibold hover:border-orange-300 hover:bg-orange-50 transition-colors text-sm"
        >
            {{ label }}
            <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute left-0 top-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-44 py-1"
        >
            <Link
                v-for="s in allSites"
                :key="s.id"
                :href="`/sites/${s.tracking_id}/${routeBase}`"
                @click="open = false"
                :class="[
                    'block px-4 py-2 text-sm hover:bg-gray-50',
                    s.id === site.id ? 'text-orange-600 font-semibold' : 'text-gray-700',
                ]"
            >
                {{ s.domain || s.name }}
            </Link>
        </div>
    </div>
</template>
