<script setup>

defineProps({
    color: {
        type: String,
        default: 'gray'
    },
    dot: {
        type: Boolean,
        default: false
    }
})

</script>

<template>

<span
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset"
    :class="{
        'bg-emerald-50 text-emerald-700 ring-emerald-200': color == 'green' || color == 'emerald',
        'bg-red-50 text-red-700 ring-red-200': color == 'red',
        'bg-blue-50 text-blue-700 ring-blue-200': color == 'blue',
        'bg-amber-50 text-amber-700 ring-amber-200': color == 'yellow' || color == 'amber',
        'bg-slate-50 text-slate-600 ring-slate-200': color == 'gray' || color == 'slate',
        'bg-indigo-50 text-indigo-700 ring-indigo-200': color == 'indigo',
        'bg-orange-50 text-orange-700 ring-orange-200': color == 'orange',
        'bg-purple-50 text-purple-700 ring-purple-200': color == 'purple'
    }"
>
    <span
        v-if="dot"
        class="w-1.5 h-1.5 rounded-full bg-current"
    />
    <slot />
</span>

</template>
