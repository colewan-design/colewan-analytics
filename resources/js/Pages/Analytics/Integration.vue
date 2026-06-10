<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SitePicker from '@/Components/SitePicker.vue'

const props = defineProps({ site: Object })
const page  = usePage()
const appUrl = page.props.appUrl ?? ''

const copy = (id) => {
    const el = document.getElementById(id)
    if (el) navigator.clipboard.writeText(el.querySelector('code').innerText)
}
</script>

<template>
    <Head :title="`${site.name} — Integration`" />

    <AppLayout>
        <div class="flex flex-col min-h-screen">

            <header class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100 gap-4 flex-wrap">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                    <Link href="/" class="hover:text-gray-800 transition-colors">Dashboard</Link>
                    <span class="text-gray-300">/</span>
                    <SitePicker :site="site" route-base="integration" />
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-semibold">Integration</span>
                </div>
            </header>

            <div class="flex-1 p-8 space-y-6 max-w-4xl">

                <!-- Tracking ID -->
                <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-orange-800">Your Tracking ID</p>
                            <p class="text-xs text-orange-600 mt-0.5">Keep this private — it identifies your site in all tracking calls.</p>
                        </div>
                    </div>
                    <code class="block mt-2 font-mono text-sm bg-white border border-orange-200 rounded-xl px-4 py-3 text-orange-700 select-all">{{ site.tracking_id }}</code>
                </div>

                <!-- Analytics snippet -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-1 text-base">1. Analytics Tracker</h2>
                    <p class="text-sm text-gray-500 mb-4">Paste this snippet into the <code class="text-orange-500 bg-orange-50 px-1 py-0.5 rounded text-xs">&lt;head&gt;</code> of every page you want to track.</p>
                    <pre id="tracker-snippet" class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;!-- Alyze Analytics --&gt;
&lt;script&gt;
window._analyticsId = '{{ site.tracking_id }}';
window._analyticsUrl = '{{ appUrl }}';
&lt;/script&gt;
&lt;script src="{{ appUrl }}/tracker.js" defer&gt;&lt;/script&gt;</code></pre>
                    <button @click="copy('tracker-snippet')" class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Copy to Clipboard</button>
                </div>

                <!-- Feedback snippet -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-1 text-base">2. Feedback Widget <span class="text-xs font-normal text-gray-400 ml-1">Optional</span></h2>
                    <p class="text-sm text-gray-500 mb-4">Add a floating satisfaction widget. Add this <strong>after</strong> the tracker script.</p>
                    <pre id="feedback-snippet" class="bg-gray-950 rounded-xl p-4 text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap font-mono leading-relaxed"><code>&lt;!-- Alyze Feedback Widget --&gt;
&lt;script src="{{ appUrl }}/feedback.js" defer&gt;&lt;/script&gt;</code></pre>
                    <button @click="copy('feedback-snippet')" class="mt-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Copy to Clipboard</button>
                    <div class="mt-4 p-3 bg-gray-50 rounded-xl text-xs text-gray-500 flex items-start gap-2">
                        <span class="text-lg shrink-0">💬</span>
                        <span>The widget shows a small prompt at the bottom-right corner: <strong>Was this page helpful?</strong> with 😊 😐 😞 buttons.</span>
                    </div>
                </div>

                <!-- SPA -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-1 text-base">3. Single-Page Apps (SPA)</h2>
                    <p class="text-sm text-gray-500 mb-4">The tracker automatically detects <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">history.pushState</code> navigation. No extra setup needed.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div v-for="fw in ['React','Vue','Next.js','Nuxt','Svelte','Angular']" :key="fw" class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl text-xs text-gray-600 font-medium">
                            <svg class="w-3 h-3 text-green-500 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                            {{ fw }}
                        </div>
                    </div>
                </div>

                <!-- API -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-semibold text-gray-900 mb-4 text-base">4. API Reference</h2>
                    <p class="text-sm text-gray-500 mb-5">All endpoints accept <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">application/json</code>. Base URL: <code class="text-xs bg-gray-100 px-1 py-0.5 rounded font-mono">{{ appUrl }}/api</code></p>
                    <div class="space-y-4">
                        <div v-for="[method, path, desc, params] in [
                            ['POST','/track/pageview','Record a page view','tracking_id, session_id, url, title?, referrer?, screen_w?, screen_h?, language?'],
                            ['POST','/track/click','Record a click event','tracking_id, session_id, page_url, element_tag?, element_text?, element_href?, x?, y?'],
                            ['POST','/track/duration','Update time-on-page','view_id, duration (seconds)'],
                            ['POST','/track/feedback','Submit page feedback','tracking_id, session_id?, page_url, rating (1–3), comment?'],
                        ]" :key="path" class="border border-gray-100 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-md">{{ method }}</span>
                                <code class="text-xs font-mono text-gray-800">{{ path }}</code>
                                <span class="text-xs text-gray-500">— {{ desc }}</span>
                            </div>
                            <div class="px-4 py-3">
                                <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700">Fields:</span> {{ params }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
