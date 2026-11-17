<script setup>

import { computed } from 'vue'

const props = defineProps({
    name: {
        type: String,
        default: '',
    },
    category: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: null,
    },
})

const tipo = computed(() => {

    const categoria = (props.category ?? '').toLowerCase()

    if (categoria.includes('bebida') || categoria.includes('jugo')) return 'bebida'
    if (categoria.includes('conserva') || categoria.includes('enlat')) return 'conserva'
    if (categoria.includes('lácteo') || categoria.includes('lacteo') || categoria.includes('queso')) return 'lacteo'
    if (categoria.includes('panific') || categoria.includes('repost')) return 'pan'
    if (categoria.includes('deshidrat')) return 'deshidratado'
    if (categoria.includes('cereal')) return 'cereal'

    return 'general'

})

const gradiente = computed(() => {

    const mapa = {
        bebida: 'bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400',
        conserva: 'bg-gradient-to-br from-rose-500 via-red-500 to-orange-500',
        lacteo: 'bg-gradient-to-br from-sky-400 via-blue-400 to-indigo-400',
        pan: 'bg-gradient-to-br from-amber-300 via-yellow-400 to-orange-400',
        deshidratado: 'bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-500',
        cereal: 'bg-gradient-to-br from-lime-400 via-green-400 to-emerald-500',
        general: 'bg-gradient-to-br from-slate-400 via-slate-500 to-slate-600',
    }

    return mapa[tipo.value] ?? mapa.general

})

const iniciales = computed(() => {

    return (props.name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((palabra) => palabra[0])
        .join('')
        .toUpperCase()

})

</script>


<template>

    <div class="relative overflow-hidden" :class="gradiente">

        <img
            v-if="image"
            :src="image"
            :alt="name"
            class="h-full w-full object-cover"
        />

        <template v-else>

            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-8 -left-4 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="absolute inset-0 flex items-center justify-center">

                <svg
                    v-if="tipo === 'bebida'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M9 2h6v3l-1 2v11a2 2 0 0 1-2 2h-0a2 2 0 0 1-2-2V7L9 5V2z" />
                    <path d="M9 5h6M9 10h6" />
                </svg>

                <svg
                    v-else-if="tipo === 'conserva'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M5 6h14v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6z" />
                    <path d="M5 6c0-1.1 3.1-2 7-2s7 .9 7 2-3.1 2-7 2-7-.9-7-2z" />
                    <path d="M8 11h8" />
                </svg>

                <svg
                    v-else-if="tipo === 'lacteo'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M9 3h6l2 4v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7l2-4z" />
                    <path d="M8 8h8M12 12v5M10 14.5h4" />
                </svg>

                <svg
                    v-else-if="tipo === 'pan'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M4 12a5 5 0 0 1 5-5h6a5 5 0 0 1 0 10H9a5 5 0 0 1-5-5z" />
                    <path d="M9 8c0 2-1 3-1 5s1 3 1 4M14 8c0 2-1 3-1 5s1 3 1 4" />
                </svg>

                <svg
                    v-else-if="tipo === 'deshidratado'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M4 8l8-4 8 4-8 4-8-4z" />
                    <path d="M4 8v8l8 4 8-4V8M12 12v8" />
                </svg>

                <svg
                    v-else-if="tipo === 'cereal'"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M12 21V7" />
                    <path d="M12 9c-3 0-5-2-5-5 3 0 5 2 5 5z" />
                    <path d="M12 13c3 0 5-2 5-5-3 0-5 2-5 5z" />
                    <path d="M12 17c-3 0-5-2-5-5 3 0 5 2 5 5z" />
                </svg>

                <svg
                    v-else
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-12 w-12 text-white/85"
                >
                    <path d="M4 7l8-4 8 4v10l-8 4-8-4V7z" />
                    <path d="M4 7l8 4 8-4M12 11v10" />
                </svg>

            </div>

            <span
                class="absolute bottom-2 right-2 rounded-md bg-white/20 px-1.5 py-0.5
                       text-[10px] font-semibold tracking-wide text-white backdrop-blur"
            >
                {{ iniciales }}
            </span>

        </template>

    </div>

</template>
