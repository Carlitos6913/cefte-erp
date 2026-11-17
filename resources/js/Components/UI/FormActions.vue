<script setup>

defineProps({
    sticky: {
        type: Boolean,
        default: true
    }
})

</script>

<template>

<div :class="sticky ? 'sticky bottom-0 z-20 -mx-4 sm:mx-0 px-4 sm:px-0 pt-3 pb-4 bg-gradient-to-t from-slate-50 via-slate-50/95 to-slate-50/0' : ''">

    <div class="bg-white/95 backdrop-blur rounded-2xl border border-slate-200 shadow-lg shadow-slate-200/40 px-4 sm:px-5 py-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
        <slot />
    </div>

</div>

</template>
