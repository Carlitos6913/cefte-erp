<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-800">
                    {{ title }}
                </h2>
                <p v-if="subtitle" class="mt-0.5 text-xs text-slate-400">
                    {{ subtitle }}
                </p>
            </div>

            <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
                <slot name="actions" />
            </div>
        </header>

        <div class="p-5">
            <slot />
        </div>
    </section>
</template>
