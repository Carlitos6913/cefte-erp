<script setup>

defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Buscar...'
    },
    fluid: {
        type: Boolean,
        default: false
    }
})

defineEmits(['update:modelValue'])

</script>

<template>

<div class="relative">

    <svg
        class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
    </svg>

    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="search"
        :placeholder="placeholder"
        class="pl-10 pr-9 py-2.5 rounded-xl border-slate-200 bg-white text-sm shadow-sm
               placeholder:text-slate-400
               focus:border-emerald-500 focus:ring-emerald-500"
        :class="fluid ? 'w-full' : 'w-full sm:w-72'"
    />

    <button
        v-if="modelValue"
        type="button"
        @click="$emit('update:modelValue', '')"
        title="Limpiar búsqueda"
        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
    >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

</div>

</template>
