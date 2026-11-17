<script setup>

defineProps({
    color: {
        type: String,
        default: 'primary'
    },
    size: {
        type: String,
        default: 'md'
    },
    type: {
        type: String,
        default: 'button'
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

</script>

<template>

<button
    :type="type"
    :disabled="disabled"
    class="inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition
           focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
           disabled:opacity-50 disabled:cursor-not-allowed"
    :class="[
        {
            'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/20 focus-visible:ring-emerald-500': color === 'primary',
            'bg-blue-600 text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus-visible:ring-blue-500': color === 'info',
            'bg-red-600 text-white shadow-sm shadow-red-600/20 hover:bg-red-700 hover:shadow-md hover:shadow-red-600/20 focus-visible:ring-red-500': color === 'danger',
            'bg-white text-slate-700 border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-slate-300 focus-visible:ring-slate-400': color === 'secondary',
            'bg-green-600 text-white shadow-sm shadow-green-600/20 hover:bg-green-700 hover:shadow-md hover:shadow-green-600/20 focus-visible:ring-green-500': color === 'success',
            'text-slate-600 hover:bg-slate-100 hover:text-slate-800 focus-visible:ring-slate-400': color === 'ghost'
        },
        {
            'px-3 py-1.5 text-xs': size === 'sm',
            'px-4 py-2.5 text-sm': size === 'md',
            'px-5 py-3 text-base': size === 'lg'
        }
    ]"
>
    <slot />
</button>

</template>
