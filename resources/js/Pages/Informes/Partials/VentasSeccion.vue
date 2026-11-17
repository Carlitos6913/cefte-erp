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
    ventas: Object,
    detalle: Object,
    esAdministrador: Boolean,
})

const serieVentas = computed(() => [props.tendencia.series[0]])

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

const maxMetodo = computed(() => Math.max(
    1,
    ...(props.ventas?.metodos_pago ?? []).map((metodo) => Number(metodo.monto ?? 0))
))

const headers = computed(() => {
    const base = ['Venta', 'Fecha', 'Cliente', 'Tipo de pago', 'Total', 'Saldo', 'Estado']

    return props.esAdministrador ? [...base, 'Registrado por'] : base
})

const colspan = computed(() => headers.value.length)

const etiquetasTipoPago = {
    contado: 'Contado',
    credito: 'Crédito',
    sin_definir: 'Sin definir',
}

const metodos = computed(() => props.ventas?.metodos_pago ?? [])

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
                :hint="`${formatearNumero(resumen.ventas.cantidad)} ventas · ${formatearNumero(resumen.ventas.unidades)} unidades`"
                :tendencia="comparativo.ventas.variacion"
                tono="emerald"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Ticket promedio"
                :value="formatearMoneda(resumen.ventas.ticket_promedio)"
                :hint="`${formatearNumero(resumen.ventas.anuladas)} ventas anuladas`"
                tono="blue"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Ventas al contado"
                :value="formatearMoneda(resumen.ventas.contado)"
                :hint="`${formatearNumero(resumen.ventas.contado_operaciones)} operaciones`"
                tono="indigo"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Ventas a crédito"
                :value="formatearMoneda(resumen.ventas.credito)"
                :hint="`${formatearNumero(resumen.ventas.credito_operaciones)} operaciones · saldo ${formatearMoneda(resumen.ventas.por_cobrar)}`"
                tono="amber"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <SectionCard title="Tendencia de ventas" subtitle="Monto vendido por periodo">
                    <TrendChart :labels="tendencia.labels" :series="serieVentas" />
                </SectionCard>
            </div>

            <SectionCard title="Formas de pago" subtitle="Distribución del periodo">
                <div class="space-y-4">
                    <div v-for="tipo in ventas.tipos_pago" :key="tipo.tipo">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-medium text-slate-600">
                                {{ etiquetasTipoPago[tipo.tipo] ?? tipo.tipo }}
                            </p>
                            <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(tipo.monto) }}</p>
                        </div>
                        <p class="mt-0.5 text-[11px] text-slate-400">{{ formatearNumero(tipo.operaciones) }} operaciones</p>
                    </div>
                    <p v-if="!ventas.tipos_pago.length" class="py-6 text-center text-sm text-slate-400">
                        Sin ventas en el periodo.
                    </p>
                </div>

                <div v-if="metodos.length" class="mt-5 border-t border-slate-100 pt-4">
                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cobros por método</p>
                    <div class="space-y-3">
                        <div v-for="metodo in metodos" :key="metodo.metodo">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-xs font-medium text-slate-600">{{ metodo.metodo }}</p>
                                <p class="shrink-0 text-xs font-bold text-slate-700">{{ formatearMoneda(metodo.monto) }}</p>
                            </div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500"
                                    :style="{ width: anchoBarra(metodo.monto, maxMetodo) }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </SectionCard>
        </div>

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
                                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500"
                                :style="{ width: anchoBarra(producto.monto, maxTopProducto) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin ventas en el periodo.</p>
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
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin clientes con compras en el periodo.</p>
            </SectionCard>
        </div>

        <SectionCard title="Ventas por categoría" subtitle="Participación de cada categoría en el periodo">
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
            <p v-else class="py-8 text-center text-sm text-slate-400">Sin ventas por categoría en el periodo.</p>
        </SectionCard>

        <div>
            <div class="mb-3 flex items-end justify-between gap-3 px-1">
                <div>
                    <h2 class="text-sm font-bold text-slate-700">Ventas registradas</h2>
                    <p class="mt-0.5 text-xs text-slate-400">{{ formatearNumero(detalle?.total ?? 0) }} registros en el periodo</p>
                </div>
            </div>

            <Table :headers="headers">
                <tr
                    v-for="venta in detalle?.data ?? []"
                    :key="venta.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/70"
                    :class="{ 'opacity-60': venta.estado !== 'activo' }"
                >
                    <td data-label="Venta" class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ venta.numero }}</p>
                    </td>
                    <td data-label="Fecha" class="px-5 py-4 text-slate-600">{{ formatearFecha(venta.fecha) }}</td>
                    <td data-label="Cliente" class="px-5 py-4 text-slate-600">
                        {{ venta.cliente_relacion?.nombre ?? venta.cliente ?? 'Consumidor final' }}
                    </td>
                    <td data-label="Tipo de pago" class="px-5 py-4">
                        <Badge :color="venta.pago_tipo === 'credito' ? 'amber' : 'green'">
                            {{ etiquetasTipoPago[venta.pago_tipo] ?? venta.pago_tipo }}
                        </Badge>
                    </td>
                    <td data-label="Total" class="px-5 py-4 font-semibold text-slate-800">{{ formatearMoneda(venta.total) }}</td>
                    <td data-label="Saldo" class="px-5 py-4">
                        <span :class="Number(venta.saldo_pendiente) > 0 ? 'font-semibold text-amber-600' : 'text-slate-400'">
                            {{ formatearMoneda(venta.saldo_pendiente) }}
                        </span>
                    </td>
                    <td data-label="Estado" class="px-5 py-4">
                        <Badge :color="venta.estado === 'activo' ? 'green' : 'red'" dot>
                            {{ venta.estado === 'activo' ? 'Activa' : 'Anulada' }}
                        </Badge>
                    </td>
                    <td v-if="esAdministrador" data-label="Registrado por" class="px-5 py-4 text-slate-600">
                        {{ venta.usuario?.name || '-' }}
                    </td>
                </tr>

                <tr v-if="!detalle?.data?.length" class="table-empty">
                    <td :colspan="colspan">
                        <EmptyState
                            title="Sin ventas para el periodo"
                            message="Prueba con otro rango de fechas o modifica los filtros aplicados."
                        />
                    </td>
                </tr>
            </Table>

            <Pagination v-if="detalle" :links="detalle.links" />
        </div>
    </div>
</template>
