<script setup>

import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    tone: {
        type: String,
        default: 'emerald'
    }
})

const tones = {
    emerald: 'bg-emerald-50 border-emerald-100 text-emerald-600',
    blue: 'bg-blue-50 border-blue-100 text-blue-600',
    amber: 'bg-amber-50 border-amber-100 text-amber-600',
    violet: 'bg-violet-50 border-violet-100 text-violet-600',
    rose: 'bg-rose-50 border-rose-100 text-rose-600',
    slate: 'bg-slate-100 border-slate-200 text-slate-600'
}

const iconClass = computed(() => tones[props.tone] ?? tones.emerald)

</script>

<template>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-slate-200/40 overflow-hidden">

    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-start gap-3">

        <div
            v-if="$slots.icon"
            class="w-8 h-8 rounded-lg border flex items-center justify-center shrink-0"
            :class="iconClass"
        >
            <slot name="icon" />
        </div>

        <div class="min-w-0">
            <h3 class="text-sm font-bold text-slate-800">
                {{ title }}
            </h3>
            <p v-if="description" class="text-xs text-slate-400 mt-0.5">
                {{ description }}
            </p>
        </div>

    </div>

    <div class="p-4 text-sm text-slate-500">
        <slot />
    </div>

</div>

</template>
