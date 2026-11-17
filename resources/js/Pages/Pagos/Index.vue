<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'


const props = defineProps({
    ventasConDeuda: Object,
    resumen: {
        type: Object,
        default: () => ({ ventasConDeuda: 0, deudaTotal: 0, pagosDelMes: 0, montoCobradoMes: 0 }),
    },
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('pagos.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}


const headers = [
    'Venta',
    'Fecha',
    'Cliente',
    'Total',
    'Saldo pendiente',
    'Acciones'
]

</script>


<template>

    <Head title="Control de pagos" />

    <AppLayout>

        <PageHeader
            title="Control de pagos"
            subtitle="Ventas a crédito con saldo pendiente de cobro"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por nro. venta o cliente..."
                />
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Ventas con deuda" :value="resumen.ventasConDeuda" hint="Créditos activos por cobrar" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 17.25h1.5M9 3.75h9a1.5 1.5 0 011.5 1.5v13.5a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5V5.25a1.5 1.5 0 011.5-1.5z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Deuda total" :value="'Bs. ' + formatoMoneda(resumen.deudaTotal)" hint="Saldo pendiente acumulado" color="red">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Pagos del mes" :value="resumen.pagosDelMes" hint="Cobros registrados este mes" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Cobrado este mes" :value="'Bs. ' + formatoMoneda(resumen.montoCobradoMes)" hint="Monto recibido en el mes" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Ventas pendientes de cobro</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ ventasConDeuda.total }} registro{{ ventasConDeuda.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="venta in ventasConDeuda.data"
                :key="venta.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
            >

                <td data-label="Venta" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">
                                {{ venta.numero }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Venta a crédito
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Fecha" class="px-5 py-4 text-slate-600">
                    {{ formatoFecha(venta.fecha) }}
                </td>

                <td data-label="Cliente" class="px-5 py-4 text-slate-600">
                    {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                </td>

                <td data-label="Total" class="px-5 py-4 font-semibold text-slate-800">
                    Bs. {{ formatoMoneda(venta.total) }}
                </td>

                <td data-label="Saldo pendiente" class="px-5 py-4">
                    <span class="font-bold text-red-600">
                        Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                    </span>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-2">

                        <Link :href="route('pagos.create', { venta_id: venta.id })">
                            <Button size="sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Registrar pago
                            </Button>
                        </Link>

                        <Link :href="route('pagos.show', venta.id)">
                            <Button color="secondary" size="sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Ver pagos
                            </Button>
                        </Link>

                    </div>
                </td>

            </tr>

            <tr v-if="ventasConDeuda.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron ventas con saldo pendiente"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Todas las ventas a crédito están al día. No hay cobros pendientes.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="ventasConDeuda.links" />

    </AppLayout>

</template>
