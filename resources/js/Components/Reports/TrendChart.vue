<script setup>
import { computed } from 'vue'

const props = defineProps({
    labels: {
        type: Array,
        default: () => [],
    },
    series: {
        type: Array,
        default: () => [],
    },
    height: {
        type: Number,
        default: 300,
    },
})

const ancho = 900
const alto = 300
const margen = { top: 18, right: 18, bottom: 38, left: 62 }
const anchoGrafico = ancho - margen.left - margen.right
const altoGrafico = alto - margen.top - margen.bottom

const formatoMoneda = (valor) => new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
}).format(Number(valor ?? 0))

const formatoEje = (valor) => {
    const numero = Number(valor ?? 0)

    if (Math.abs(numero) >= 1000000) return `${(numero / 1000000).toFixed(1)}M`
    if (Math.abs(numero) >= 1000) return `${(numero / 1000).toFixed(1)}k`

    return numero.toFixed(0)
}

const totalSeries = computed(() => props.series.reduce(
    (total, serie) => total + (serie.valores ?? []).reduce((suma, valor) => suma + Number(valor ?? 0), 0),
    0
))

const sinDatos = computed(() => {
    return !props.labels.length || !props.series.length || totalSeries.value <= 0
})

const maximo = computed(() => {
    const valores = props.series.flatMap((serie) => (serie.valores ?? []).map(Number))
    const max = Math.max(0, ...valores)

    if (max <= 0) return 1

    const potencia = 10 ** Math.floor(Math.log10(max))
    const normalizado = max / potencia
    const factores = [1, 1.5, 2, 2.5, 3, 4, 5, 7.5, 10]
    const factor = factores.find((valor) => normalizado <= valor) ?? 10

    return factor * potencia
})

const ticks = computed(() => {
    return [1, 0.75, 0.5, 0.25, 0]
        .map((proporcion) => ({
            valor: maximo.value * proporcion,
            y: margen.top + altoGrafico * (1 - proporcion),
        }))
})

const posicionX = (indice) => {
    if (props.labels.length <= 1) return margen.left + anchoGrafico / 2

    return margen.left + (indice / (props.labels.length - 1)) * anchoGrafico
}

const posicionY = (valor) => {
    return margen.top + altoGrafico - (Number(valor ?? 0) / maximo.value) * altoGrafico
}

const seriesCalculadas = computed(() => {
    return props.series.map((serie) => {
        const puntos = (serie.valores ?? []).map((valor, indice) => ({
            x: posicionX(indice),
            y: posicionY(valor),
            valor: Number(valor ?? 0),
            etiqueta: props.labels[indice] ?? '',
        }))

        return {
            ...serie,
            puntos,
            trazo: puntos
                .map((punto, indice) => `${indice === 0 ? 'M' : 'L'} ${punto.x.toFixed(2)} ${punto.y.toFixed(2)}`)
                .join(' '),
        }
    })
})

const pasoEtiquetas = computed(() => Math.max(1, Math.ceil(props.labels.length / 8)))
</script>

<template>
    <div class="relative w-full">
        <div class="mb-4 flex flex-wrap items-center gap-4">
            <div
                v-for="serie in series"
                :key="serie.nombre"
                class="flex items-center gap-2 text-xs font-medium text-slate-500"
            >
                <span
                    class="h-2.5 w-2.5 rounded-full"
                    :style="{ backgroundColor: serie.color }"
                />
                {{ serie.nombre }}
            </div>
        </div>

        <div v-if="sinDatos" class="flex h-[260px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
            <svg class="h-9 w-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <p class="mt-3 text-sm font-semibold text-slate-500">
                Sin movimientos en el periodo
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Prueba con otro rango de fechas.
            </p>
        </div>

        <svg
            v-else
            :viewBox="`0 0 ${ancho} ${alto}`"
            class="h-auto w-full"
            :style="{ maxHeight: `${height}px` }"
            role="img"
            aria-label="Evolución del periodo"
        >
            <g>
                <line
                    v-for="tick in ticks"
                    :key="`linea-${tick.y}`"
                    :x1="margen.left"
                    :x2="ancho - margen.right"
                    :y1="tick.y"
                    :y2="tick.y"
                    stroke="#e2e8f0"
                    stroke-width="1"
                />
                <text
                    v-for="tick in ticks"
                    :key="`valor-${tick.y}`"
                    :x="margen.left - 10"
                    :y="tick.y + 3"
                    text-anchor="end"
                    font-size="11"
                    fill="#94a3b8"
                >
                    {{ formatoEje(tick.valor) }}
                </text>
            </g>

            <g>
                <template v-for="(etiqueta, indice) in labels" :key="`etiqueta-${indice}`">
                    <text
                        v-if="indice % pasoEtiquetas === 0 || indice === labels.length - 1"
                        :x="posicionX(indice)"
                        :y="alto - 12"
                        text-anchor="middle"
                        font-size="11"
                        fill="#94a3b8"
                    >
                        {{ etiqueta }}
                    </text>
                </template>
            </g>

            <g v-for="serie in seriesCalculadas" :key="serie.nombre">
                <path
                    :d="serie.trazo"
                    fill="none"
                    :stroke="serie.color"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    vector-effect="non-scaling-stroke"
                />

                <template v-for="(punto, indice) in serie.puntos" :key="`${serie.nombre}-${indice}`">
                    <circle
                        :cx="punto.x"
                        :cy="punto.y"
                        r="3"
                        :fill="serie.color"
                        stroke="#ffffff"
                        stroke-width="1.5"
                    >
                        <title>{{ punto.etiqueta }} · {{ serie.nombre }}: Bs. {{ formatoMoneda(punto.valor) }}</title>
                    </circle>
                </template>
            </g>
        </svg>
    </div>
</template>
