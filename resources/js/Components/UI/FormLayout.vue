<script setup>

defineProps({
    aside: {
        type: Boolean,
        default: false
    }
})

</script>

<template>

<div :class="aside ? 'grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_20rem] gap-6 items-start' : ''">

    <div class="space-y-5 min-w-0">
        <slot />
    </div>

    <aside v-if="aside" class="space-y-5 xl:sticky xl:top-6">
        <slot name="aside" />
    </aside>

</div>

</template>
