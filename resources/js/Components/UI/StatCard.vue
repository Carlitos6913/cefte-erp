<script setup>

defineProps({
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        default: '-'
    },
    hint: {
        type: String,
        default: ''
    },
    color: {
        type: String,
        default: 'emerald'
    }
})

</script>

<template>

<div class="relative bg-white rounded-2xl border border-slate-200 p-5 overflow-hidden hover:shadow-md transition-shadow duration-200">

    <div
        class="absolute -top-8 -right-8 w-28 h-28 rounded-full opacity-10"
        :class="{
            'bg-emerald-500': color === 'emerald',
            'bg-blue-500': color === 'blue',
            'bg-amber-500': color === 'amber',
            'bg-red-500': color === 'red',
            'bg-indigo-500': color === 'indigo',
            'bg-slate-500': color === 'slate'
        }"
    />

    <div class="relative flex items-start justify-between gap-3">

        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                {{ label }}
            </p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-800 truncate">
                {{ value }}
            </p>
            <p v-if="hint" class="mt-1 text-xs text-slate-400">
                {{ hint }}
            </p>
        </div>

        <div
            v-if="$slots.icon"
            class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
            :class="{
                'bg-emerald-50 text-emerald-600': color === 'emerald',
                'bg-blue-50 text-blue-600': color === 'blue',
                'bg-amber-50 text-amber-600': color === 'amber',
                'bg-red-50 text-red-600': color === 'red',
                'bg-indigo-50 text-indigo-600': color === 'indigo',
                'bg-slate-100 text-slate-600': color === 'slate'
            }"
        >
            <slot name="icon" />
        </div>

    </div>

</div>

</template>
