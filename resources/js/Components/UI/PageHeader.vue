<script setup>

defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    }
})

</script>

<template>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

    <div class="flex items-start gap-3 min-w-0">

        <div
            v-if="$slots.icon"
            class="hidden sm:flex w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 items-center justify-center text-emerald-600 shrink-0"
        >
            <slot name="icon" />
        </div>

        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-800">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-sm text-slate-500 mt-1">
                {{ subtitle }}
            </p>
        </div>

    </div>

    <div
        v-if="$slots.actions"
        class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0"
    >
        <slot name="actions" />
    </div>

</div>

</template>
