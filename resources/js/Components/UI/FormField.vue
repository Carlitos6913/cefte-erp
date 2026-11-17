<script setup>

defineProps({
    label: {
        type: String,
        required: true
    },
    forId: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: null
    },
    hint: {
        type: String,
        default: null
    },
    required: {
        type: Boolean,
        default: false
    },
    optional: {
        type: Boolean,
        default: false
    }
})

</script>

<template>

<div>

    <label
        :for="forId"
        class="flex items-center gap-1 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"
    >
        <span>{{ label }}</span>
        <span v-if="required" class="text-red-500">*</span>
        <span v-else-if="optional" class="text-[10px] font-medium normal-case tracking-normal text-slate-400">
            (opcional)
        </span>
    </label>

    <slot />

    <p v-if="error" class="flex items-start gap-1.5 text-xs font-medium text-red-500 mt-1.5">
        <svg class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <span>{{ error }}</span>
    </p>

    <p v-else-if="hint" class="text-xs text-slate-400 mt-1.5">
        {{ hint }}
    </p>

</div>

</template>
