<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({ links: Array })
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap gap-1 mt-5">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                :class="[
                    'px-3 py-1.5 text-xs rounded-lg border transition-colors',
                    link.active
                        ? 'bg-orange-500 text-white border-orange-500'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300',
                ]"
                v-html="link.label"
            />
            <span
                v-else
                class="px-3 py-1.5 text-xs rounded-lg border border-gray-100 text-gray-300"
                v-html="link.label"
            />
        </template>
    </div>
</template>
