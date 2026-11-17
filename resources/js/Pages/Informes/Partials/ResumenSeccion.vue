<script setup>
import { computed } from 'vue'
import MetricCard from '@/Components/Reports/MetricCard.vue'
import SectionCard from '@/Components/Reports/SectionCard.vue'
import TrendChart from '@/Components/Reports/TrendChart.vue'
import { formatearMoneda, formatearNumero } from '@/utils/informes'

const props = defineProps({
    resumen: Object,
    comparativo: Object,
    tendencia: Object,
    ventas: Object,
    inventario: Object,
    cobranzas: Object,
})

const maxCategoria = computed(() => Math.max(
    1,
    ...(props.ventas?.categorias ?? []).map((categoria) => Number(categoria.monto ?? 0))
))

const maxTopProducto = computed(() => Math.max(
    1,
    ...(props.ventas?.top_productos ?? []).map((producto) => Number(producto.monto ?? 0))
))

const maxTopCliente = computed(() => Math.max(
    1,
    ...(props.ventas?.top_clientes ?? []).map((cliente) => Number(cliente.monto ?? 0))
))

const tonosAntiguedad = {
    '0_30': 'bg-emerald-500',
    '31_60': 'bg-blue-500',
    '61_90': 'bg-amber-500',
    mas_90: 'bg-red-500',
}

const anchoBarra = (monto, maximo) => {
    return `${Math.max(4, Math.round((Number(monto ?? 0) / maximo) * 100))}%`
}
</script>

<template>
    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <MetricCard
                label="Ventas del periodo"
                :value="formatearMoneda(resumen.ventas.total)"
                :hint="`${formatearNumero(resumen.ventas.cantidad)} ventas · ticket ${formatearMoneda(resumen.ventas.ticket_promedio)}`"
                :tendencia="comparativo.ventas.variacion"
                tono="emerald"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Cobrado en el periodo"
                :value="formatearMoneda(resumen.cobros.total)"
                :hint="`${formatearNumero(resumen.cobros.cantidad)} pagos registrados`"
                :tendencia="comparativo.cobros.variacion"
                tono="blue"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6.75A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V12m18 0V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75V12" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Compras del periodo"
                :value="formatearMoneda(resumen.compras.total)"
                :hint="`${formatearNumero(resumen.compras.cantidad)} ingresos · ${formatearNumero(resumen.compras.unidades)} unidades`"
                :tendencia="comparativo.compras.variacion"
                tono="indigo"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Cuentas por cobrar"
                :value="formatearMoneda(resumen.ventas.por_cobrar)"
                :hint="`Saldo actual · ${formatearNumero(resumen.ventas.credito_pendiente_operaciones)} operaciones · ${formatearNumero(resumen.ventas.clientes_con_deuda)} clientes`"
                tono="amber"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Inventario al costo</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">{{ formatearMoneda(resumen.inventario.valor) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">Comercial: {{ formatearMoneda(resumen.inventario.valor_comercial) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Stock disponible</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">{{ formatearNumero(resumen.inventario.stock_total) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ formatearNumero(resumen.inventario.presentaciones) }} presentaciones activas</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Stock bajo</p>
                <p class="mt-2 text-lg font-extrabold" :class="resumen.inventario.stock_bajo > 0 ? 'text-amber-600' : 'text-slate-800'">
                    {{ formatearNumero(resumen.inventario.stock_bajo) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">Presentaciones en o bajo el mínimo</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Por vencer</p>
                <p class="mt-2 text-lg font-extrabold" :class="resumen.inventario.lotes_por_vencer > 0 ? 'text-red-600' : 'text-slate-800'">
                    {{ formatearNumero(resumen.inventario.lotes_por_vencer) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    {{ formatearNumero(resumen.inventario.lotes_vencidos) }} ya vencidos
                </p>
            </div>
        </div>

        <SectionCard
            title="Evolución del periodo"
            subtitle="Ventas, compras y cobros agrupados según el rango seleccionado"
        >
            <TrendChart :labels="tendencia.labels" :series="tendencia.series" />
        </SectionCard>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <SectionCard title="Productos más vendidos" subtitle="Top 10 por monto vendido">
                <div v-if="ventas.top_productos.length" class="space-y-4">
                    <div v-for="(producto, indice) in ventas.top_productos" :key="producto.id">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                    {{ indice + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700">{{ producto.producto }}</p>
                                    <p class="truncate text-xs text-slate-400">
                                        {{ producto.presentacion || producto.sku }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(producto.monto) }}</p>
                                <p class="text-[11px] text-slate-400">
                                    {{ formatearNumero(producto.cantidad) }} {{ producto.unidad }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500"
                                :style="{ width: anchoBarra(producto.monto, maxTopProducto) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">
                    Sin ventas registradas en el periodo.
                </p>
            </SectionCard>

            <SectionCard title="Clientes con mayor compra" subtitle="Top 10 por monto comprado">
                <div v-if="ventas.top_clientes.length" class="space-y-4">
                    <div v-for="(cliente, indice) in ventas.top_clientes" :key="`${cliente.cliente}-${indice}`">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                    {{ indice + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700">{{ cliente.cliente }}</p>
                                    <p class="text-xs text-slate-400">{{ formatearNumero(cliente.operaciones) }} operaciones</p>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(cliente.monto) }}</p>
                                <p v-if="cliente.saldo > 0" class="text-[11px] font-medium text-amber-600">
                                    Saldo {{ formatearMoneda(cliente.saldo) }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-500"
                                :style="{ width: anchoBarra(cliente.monto, maxTopCliente) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">
                    Sin clientes con compras en el periodo.
                </p>
            </SectionCard>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <SectionCard title="Ventas por categoría" subtitle="Participación dentro del periodo">
                <div v-if="ventas.categorias.length" class="space-y-4">
                    <div v-for="categoria in ventas.categorias" :key="categoria.categoria">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-medium text-slate-600">{{ categoria.categoria }}</p>
                            <p class="shrink-0 text-sm font-bold text-slate-800">{{ formatearMoneda(categoria.monto) }}</p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-purple-500"
                                :style="{ width: anchoBarra(categoria.monto, maxCategoria) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">
                    Sin ventas por categoría en el periodo.
                </p>
            </SectionCard>

            <SectionCard title="Antigüedad de la cartera" subtitle="Saldo pendiente actual de ventas a crédito">
                <div class="space-y-4">
                    <div v-for="bucket in cobranzas.antiguedad" :key="bucket.clave">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :class="tonosAntiguedad[bucket.clave]" />
                                <p class="text-sm font-medium text-slate-600">{{ bucket.etiqueta }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(bucket.monto) }}</p>
                                <p class="text-[11px] text-slate-400">
                                    {{ formatearNumero(bucket.operaciones) }} operaciones · {{ bucket.porcentaje }}%
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full"
                                :class="tonosAntiguedad[bucket.clave]"
                                :style="{ width: `${Math.max(bucket.monto > 0 ? 3 : 0, bucket.porcentaje)}%` }"
                            />
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total pendiente</p>
                    <p class="text-base font-extrabold text-slate-800">{{ formatearMoneda(cobranzas.total_cartera) }}</p>
                </div>
            </SectionCard>
        </div>
    </div>
</template>
