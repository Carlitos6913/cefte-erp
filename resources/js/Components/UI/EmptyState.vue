<script setup>

defineProps({
    title: {
        type: String,
        default: 'Sin resultados'
    },
    message: {
        type: String,
        default: ''
    }
})

</script>

<template>

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">

    <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300">
        <slot name="icon">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </slot>
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-700">
        {{ title }}
    </h3>

    <p v-if="message" class="mt-1 text-sm text-slate-400 max-w-sm">
        {{ message }}
    </p>

    <div v-if="$slots.action" class="mt-5">
        <slot name="action" />
    </div>

</div>

</template>
