<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: '-',
    },
    hint: {
        type: String,
        default: '',
    },
    tendencia: {
        type: Number,
        default: null,
    },
    tendenciaTexto: {
        type: String,
        default: 'vs periodo anterior',
    },
    tono: {
        type: String,
        default: 'emerald',
    },
})

const tonos = {
    emerald: {
        barra: 'bg-gradient-to-r from-emerald-400 to-teal-500',
        icono: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
    },
    blue: {
        barra: 'bg-gradient-to-r from-blue-400 to-indigo-500',
        icono: 'bg-blue-50 text-blue-600 ring-blue-100',
    },
    amber: {
        barra: 'bg-gradient-to-r from-amber-400 to-orange-500',
        icono: 'bg-amber-50 text-amber-600 ring-amber-100',
    },
    red: {
        barra: 'bg-gradient-to-r from-red-400 to-rose-500',
        icono: 'bg-red-50 text-red-600 ring-red-100',
    },
    indigo: {
        barra: 'bg-gradient-to-r from-indigo-400 to-purple-500',
        icono: 'bg-indigo-50 text-indigo-600 ring-indigo-100',
    },
    slate: {
        barra: 'bg-gradient-to-r from-slate-400 to-slate-500',
        icono: 'bg-slate-100 text-slate-600 ring-slate-200',
    },
}

const estilo = computed(() => tonos[props.tono] ?? tonos.slate)

const tendenciaPositiva = computed(() => Number(props.tendencia) >= 0)

const etiquetaTendencia = computed(() => {
    if (props.tendencia === null || props.tendencia === undefined) return null
    const valor = Number(props.tendencia)
    return `${valor > 0 ? '+' : ''}${valor.toFixed(1)}%`
})
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="absolute inset-x-0 top-0 h-1" :class="estilo.barra" />

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    {{ label }}
                </p>
                <p class="mt-2 truncate text-2xl font-extrabold tracking-tight text-slate-800">
                    {{ value }}
                </p>
            </div>

            <div
                v-if="$slots.icon"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset"
                :class="estilo.icono"
            >
                <slot name="icon" />
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span
                v-if="etiquetaTendencia"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 ring-inset"
                :class="tendenciaPositiva
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                    : 'bg-red-50 text-red-700 ring-red-200'"
            >
                <svg
                    class="h-3 w-3"
                    :class="{ 'rotate-180': tendenciaPositiva }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2.5"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                </svg>
                {{ etiquetaTendencia }}
            </span>
            <span v-if="hint" class="text-xs text-slate-400">
                {{ hint }}
            </span>
        </div>

        <p v-if="etiquetaTendencia && tendenciaTexto" class="mt-1 text-[11px] text-slate-400">
            {{ tendenciaTexto }}
        </p>
    </div>
</template>
