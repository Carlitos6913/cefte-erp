<script setup>
import { computed } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Table from '@/Components/UI/Table.vue'
import MetricCard from '@/Components/Reports/MetricCard.vue'
import SectionCard from '@/Components/Reports/SectionCard.vue'
import TrendChart from '@/Components/Reports/TrendChart.vue'
import { formatearFecha, formatearMoneda, formatearNumero } from '@/utils/informes'

const props = defineProps({
    resumen: Object,
    comparativo: Object,
    tendencia: Object,
    ingresos: Object,
    detalle: Object,
    esAdministrador: Boolean,
})

const serieCompras = computed(() => [props.tendencia.series[1]])

const promedioCompra = computed(() => {
    if (!props.resumen.compras.cantidad) return 0

    return props.resumen.compras.total / props.resumen.compras.cantidad
})

const maxTopProducto = computed(() => Math.max(
    1,
    ...(props.ingresos?.top_productos ?? []).map((producto) => Number(producto.monto ?? 0))
))

const maxEncargado = computed(() => Math.max(
    1,
    ...(props.ingresos?.encargados ?? []).map((encargado) => Number(encargado.monto ?? 0))
))

const headers = computed(() => {
    const base = ['Ingreso', 'Fecha', 'Encargado', 'Productos', 'Total', 'Estado']

    return props.esAdministrador ? [...base, 'Registrado por'] : base
})

const colspan = computed(() => headers.value.length)

const anchoBarra = (monto, maximo) => {
    return `${Math.max(4, Math.round((Number(monto ?? 0) / maximo) * 100))}%`
}
</script>

<template>
    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <MetricCard
                label="Compras del periodo"
                :value="formatearMoneda(resumen.compras.total)"
                :hint="`${formatearNumero(resumen.compras.cantidad)} ingresos registrados`"
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
                label="Unidades ingresadas"
                :value="formatearNumero(resumen.compras.unidades)"
                :hint="`Promedio ${formatearMoneda(promedioCompra)} por ingreso`"
                tono="emerald"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Ingresos anulados"
                :value="formatearNumero(resumen.compras.anuladas)"
                hint="Movimientos sin efecto en inventario"
                tono="red"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Encargados del periodo"
                :value="formatearNumero(ingresos.encargados.length)"
                hint="Responsables con ingresos registrados"
                tono="blue"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <SectionCard title="Tendencia de compras" subtitle="Monto ingresado por periodo">
            <TrendChart :labels="tendencia.labels" :series="serieCompras" />
        </SectionCard>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <SectionCard title="Productos más comprados" subtitle="Top 10 por monto ingresado">
                <div v-if="ingresos.top_productos.length" class="space-y-4">
                    <div v-for="(producto, indice) in ingresos.top_productos" :key="producto.id">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                    {{ indice + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700">{{ producto.producto }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ producto.presentacion || producto.sku }}</p>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(producto.monto) }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatearNumero(producto.cantidad) }} {{ producto.unidad }}</p>
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-purple-500"
                                :style="{ width: anchoBarra(producto.monto, maxTopProducto) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin compras en el periodo.</p>
            </SectionCard>

            <SectionCard title="Compras por encargado" subtitle="Monto registrado por responsable">
                <div v-if="ingresos.encargados.length" class="space-y-4">
                    <div v-for="encargado in ingresos.encargados" :key="encargado.encargado">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-600">{{ encargado.encargado }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatearNumero(encargado.ingresos) }} ingresos</p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-slate-800">{{ formatearMoneda(encargado.monto) }}</p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500"
                                :style="{ width: anchoBarra(encargado.monto, maxEncargado) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin ingresos en el periodo.</p>
            </SectionCard>
        </div>

        <div>
            <div class="mb-3 flex items-end justify-between gap-3 px-1">
                <div>
                    <h2 class="text-sm font-bold text-slate-700">Ingresos registrados</h2>
                    <p class="mt-0.5 text-xs text-slate-400">{{ formatearNumero(detalle?.total ?? 0) }} registros en el periodo</p>
                </div>
            </div>

            <Table :headers="headers">
                <tr
                    v-for="ingreso in detalle?.data ?? []"
                    :key="ingreso.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/70"
                    :class="{ 'opacity-60': ingreso.estado !== 'activo' }"
                >
                    <td data-label="Ingreso" class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ ingreso.numero }}</p>
                    </td>
                    <td data-label="Fecha" class="px-5 py-4 text-slate-600">{{ formatearFecha(ingreso.fecha) }}</td>
                    <td data-label="Encargado" class="px-5 py-4 text-slate-600">{{ ingreso.encargado?.full_name || '-' }}</td>
                    <td data-label="Productos" class="px-5 py-4 text-slate-600">
                        {{ formatearNumero(ingreso.detalles_count) }} producto(s)
                    </td>
                    <td data-label="Total" class="px-5 py-4 font-semibold text-slate-800">{{ formatearMoneda(ingreso.total) }}</td>
                    <td data-label="Estado" class="px-5 py-4">
                        <Badge :color="ingreso.estado === 'activo' ? 'green' : 'red'" dot>
                            {{ ingreso.estado === 'activo' ? 'Activo' : 'Anulado' }}
                        </Badge>
                    </td>
                    <td v-if="esAdministrador" data-label="Registrado por" class="px-5 py-4 text-slate-600">
                        {{ ingreso.usuario?.name || '-' }}
                    </td>
                </tr>

                <tr v-if="!detalle?.data?.length" class="table-empty">
                    <td :colspan="colspan">
                        <EmptyState
                            title="Sin ingresos para el periodo"
                            message="Prueba con otro rango de fechas o modifica los filtros aplicados."
                        />
                    </td>
                </tr>
            </Table>

            <Pagination v-if="detalle" :links="detalle.links" />
        </div>
    </div>
</template>
