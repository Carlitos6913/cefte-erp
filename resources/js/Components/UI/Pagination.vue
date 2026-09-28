<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({ links: Array })
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap justify-center gap-1.5 mt-6">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                preserve-scroll
                preserve-state
                class="px-3 py-2 rounded-lg border text-sm transition"
                :class="link.active
                    ? 'bg-emerald-600 text-white border-emerald-600'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            />
            <span
                v-else
                v-html="link.label"
                class="px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-300 bg-slate-50"
            />
        </template>
    </div>
</template>