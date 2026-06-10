<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const flash   = computed(() => page.props.flash)
const site    = computed(() => page.props.site)
const tid     = computed(() => site.value?.tracking_id)
const comp    = computed(() => page.component)

const siteHref = (suffix) => tid.value ? `/sites/${tid.value}/${suffix}` : '#'

const navCls = (active) =>
    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors ' +
    (active
        ? 'bg-orange-50 text-orange-600 font-semibold'
        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 font-medium')

const logout = () => router.post('/logout')

const mobileOpen = ref(false)
</script>

<template>
    <div class="bg-gray-50 text-gray-900 min-h-screen flex">

        <!-- Flash -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="flash?.success" class="fixed top-4 right-4 z-50 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm shadow-md max-w-sm">
                {{ flash.success }}
            </div>
        </Transition>

        <!-- Mobile overlay -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="mobileOpen" class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="mobileOpen = false"></div>
        </Transition>

        <!-- Sidebar -->
        <aside :class="[
            'w-60 bg-white border-r border-gray-100 min-h-screen flex flex-col fixed left-0 top-0 z-40 transition-transform duration-300 ease-in-out',
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]">

            <!-- Logo -->
            <div class="px-5 pt-6 pb-5 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2.5" @click="mobileOpen = false">
                    <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-sm shrink-0">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="12" width="4" height="9" rx="1.5"/>
                            <rect x="10" y="7" width="4" height="14" rx="1.5"/>
                            <rect x="17" y="3" width="4" height="18" rx="1.5"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-gray-900">Alyse</span>
                </Link>
                <button @click="mobileOpen = false" class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 space-y-0.5 overflow-y-auto pb-4">

                <Link href="/" :class="navCls(comp === 'Dashboard/Index')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <rect x="2" y="2" width="7" height="7" rx="1.5"/>
                        <rect x="11" y="2" width="7" height="7" rx="1.5"/>
                        <rect x="2" y="11" width="7" height="7" rx="1.5"/>
                        <rect x="11" y="11" width="7" height="7" rx="1.5"/>
                    </svg>
                    Dashboard
                </Link>

                <Link href="/boards" :class="navCls(comp === 'Boards/Index')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    Boards
                </Link>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Insight</p>

                <Link :href="siteHref('charts')" :class="navCls(comp === 'Analytics/Charts')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3h18v14H3z"/><path d="M7 17v2M17 17v2M7 9l3 3 3-3 4 4"/>
                    </svg>
                    Charts
                </Link>

                <Link :href="siteHref('replay')" :class="navCls(comp === 'Analytics/Replay')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 3l14 9-14 9V3z"/>
                    </svg>
                    Replay
                </Link>

                <Link :href="siteHref('heatmap')" :class="navCls(comp === 'Analytics/Heatmap')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C9 5 7 8 7 11a5 5 0 0010 0c0-3-2-6-5-9z"/>
                    </svg>
                    Heatmap
                </Link>

                <Link :href="siteHref('feedback')" :class="navCls(comp === 'Analytics/Feedback')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Feedback
                </Link>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Data</p>

                <Link :href="siteHref('visitors')" :class="navCls(comp === 'Analytics/Visitors')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    Visitors
                </Link>

                <Link :href="siteHref('events')" :class="navCls(comp === 'Analytics/Events')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 15l-7-7M8.5 8.5v7h7"/>
                    </svg>
                    Events Click
                </Link>

                <Link :href="siteHref('integration')" :class="navCls(comp === 'Analytics/Integration')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                    </svg>
                    Integration
                </Link>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 pt-5 pb-1.5">Manage</p>

                <Link :href="siteHref('workspace')" :class="navCls(comp === 'Workspace/Index')" @click="mobileOpen = false">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM9 22V12h6v10"/>
                    </svg>
                    Workspace
                </Link>

            </nav>

            <!-- Logout -->
            <div class="px-3 pb-5 pt-3 border-t border-gray-100">
                <button @click="logout" class="flex items-center gap-3 px-3 py-2.5 rounded-xl w-full text-left text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-700 font-medium transition-colors">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                    Logout
                </button>
            </div>
        </aside>

        <!-- Main wrapper -->
        <div class="flex-1 flex flex-col md:ml-60 min-w-0">

            <!-- Mobile top bar -->
            <div class="md:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100 sticky top-0 z-20">
                <button @click="mobileOpen = true" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-orange-500 rounded-lg flex items-center justify-center shadow-sm shrink-0">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="12" width="4" height="9" rx="1.5"/>
                            <rect x="10" y="7" width="4" height="14" rx="1.5"/>
                            <rect x="17" y="3" width="4" height="18" rx="1.5"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg tracking-tight text-gray-900">Alyse</span>
                </Link>
                <div class="w-9"></div>
            </div>

            <main class="flex-1 min-h-screen">
                <slot />
            </main>
        </div>
    </div>
</template>
