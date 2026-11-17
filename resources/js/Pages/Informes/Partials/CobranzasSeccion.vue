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
    cobranzas: Object,
    detalle: Object,
    esAdministrador: Boolean,
})

const serieCobros = computed(() => [props.tendencia.series[2]])

const carteraVencida = computed(() => {
    const bucket = props.cobranzas?.antiguedad?.find((item) => item.clave === 'mas_90')

    return Number(bucket?.monto ?? 0)
})

const maxDeudor = computed(() => Math.max(
    1,
    ...(props.cobranzas?.top_deudores ?? []).map((deudor) => Number(deudor.saldo ?? 0))
))

const maxMetodo = computed(() => Math.max(
    1,
    ...(props.cobranzas?.metodos_pago ?? []).map((metodo) => Number(metodo.monto ?? 0))
))

const headers = computed(() => {
    const base = ['Venta', 'Fecha', 'Cliente', 'Método', 'Monto', 'Saldo de la venta']

    return props.esAdministrador ? [...base, 'Registrado por'] : base
})

const colspan = computed(() => headers.value.length)

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
                label="Total por cobrar"
                :value="formatearMoneda(cobranzas.total_cartera)"
                :hint="`Saldo actual · ${formatearNumero(resumen.ventas.credito_pendiente_operaciones)} operaciones a crédito`"
                tono="amber"
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
                tono="emerald"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6.75A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V12m18 0V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75V12" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Cartera mayor a 90 días"
                :value="formatearMoneda(carteraVencida)"
                hint="Saldo con mayor antigüedad"
                tono="red"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.052 3.378c.866-1.5 3.032-1.5 3.898 0l7.353 12.748zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Clientes con deuda"
                :value="formatearNumero(resumen.ventas.clientes_con_deuda)"
                hint="Clientes registrados con saldo pendiente"
                tono="blue"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <SectionCard title="Tendencia de cobros" subtitle="Monto cobrado por periodo">
                    <TrendChart :labels="tendencia.labels" :series="serieCobros" />
                </SectionCard>
            </div>

            <SectionCard title="Cobros por método" subtitle="Distribución del periodo">
                <div v-if="cobranzas.metodos_pago.length" class="space-y-4">
                    <div v-for="metodo in cobranzas.metodos_pago" :key="metodo.metodo">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-medium text-slate-600">{{ metodo.metodo }}</p>
                            <p class="shrink-0 text-sm font-bold text-slate-800">{{ formatearMoneda(metodo.monto) }}</p>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500"
                                    :style="{ width: anchoBarra(metodo.monto, maxMetodo) }"
                                />
                            </div>
                            <p class="shrink-0 text-[11px] text-slate-400">{{ formatearNumero(metodo.operaciones) }} pagos</p>
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin cobros en el periodo.</p>
            </SectionCard>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <SectionCard title="Antigüedad de la cartera" subtitle="Saldo pendiente actual agrupado por antigüedad">
                <div class="space-y-4">
                    <div v-for="bucket in cobranzas.antiguedad" :key="bucket.clave">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :class="tonosAntiguedad[bucket.clave]" />
                                <p class="text-sm font-medium text-slate-600">{{ bucket.etiqueta }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(bucket.monto) }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatearNumero(bucket.operaciones) }} operaciones · {{ bucket.porcentaje }}%</p>
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

            <SectionCard title="Mayores deudores" subtitle="Top 10 por saldo pendiente">
                <div v-if="cobranzas.top_deudores.length" class="space-y-4">
                    <div v-for="(deudor, indice) in cobranzas.top_deudores" :key="`${deudor.cliente}-${indice}`">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                    {{ indice + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700">{{ deudor.cliente }}</p>
                                    <p class="text-xs text-slate-400">{{ formatearNumero(deudor.operaciones) }} operaciones</p>
                                </div>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-slate-800">{{ formatearMoneda(deudor.saldo) }}</p>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-amber-400 to-red-500"
                                :style="{ width: anchoBarra(deudor.saldo, maxDeudor) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin saldos pendientes.</p>
            </SectionCard>
        </div>

        <div>
            <div class="mb-3 flex items-end justify-between gap-3 px-1">
                <div>
                    <h2 class="text-sm font-bold text-slate-700">Cobros registrados</h2>
                    <p class="mt-0.5 text-xs text-slate-400">{{ formatearNumero(detalle?.total ?? 0) }} pagos en el periodo</p>
                </div>
            </div>

            <Table :headers="headers">
                <tr
                    v-for="pago in detalle?.data ?? []"
                    :key="pago.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/70"
                >
                    <td data-label="Venta" class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ pago.venta?.numero ?? '-' }}</p>
                    </td>
                    <td data-label="Fecha" class="px-5 py-4 text-slate-600">{{ formatearFecha(pago.fecha) }}</td>
                    <td data-label="Cliente" class="px-5 py-4 text-slate-600">
                        {{ pago.venta?.cliente_relacion?.nombre ?? pago.venta?.cliente ?? 'Consumidor final' }}
                    </td>
                    <td data-label="Método" class="px-5 py-4">
                        <Badge color="blue">{{ pago.metodo || 'Sin especificar' }}</Badge>
                    </td>
                    <td data-label="Monto" class="px-5 py-4 font-semibold text-emerald-700">{{ formatearMoneda(pago.monto) }}</td>
                    <td data-label="Saldo de la venta" class="px-5 py-4">
                        <span :class="Number(pago.venta?.saldo_pendiente) > 0 ? 'font-semibold text-amber-600' : 'text-slate-400'">
                            {{ formatearMoneda(pago.venta?.saldo_pendiente ?? 0) }}
                        </span>
                    </td>
                    <td v-if="esAdministrador" data-label="Registrado por" class="px-5 py-4 text-slate-600">
                        {{ pago.usuario?.name || '-' }}
                    </td>
                </tr>

                <tr v-if="!detalle?.data?.length" class="table-empty">
                    <td :colspan="colspan">
                        <EmptyState
                            title="Sin cobros para el periodo"
                            message="Prueba con otro rango de fechas o modifica los filtros aplicados."
                        />
                    </td>
                </tr>
            </Table>

            <Pagination v-if="detalle" :links="detalle.links" />
        </div>
    </div>
</template>
