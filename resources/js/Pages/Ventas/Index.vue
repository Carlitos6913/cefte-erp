<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import { presentacionVariante } from '@/utils/producto'


defineProps({
    ventas: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, montoTotal: 0, saldoPendiente: 0, credito: 0 }),
    },
})


const headers = [
    'Productos',
    'Fecha',
    'Cliente',
    'Total',
    'Pago',
    'Estado',
    'Acciones'
]


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}

const nombresProductos = (detalles = []) => {
    return (detalles ?? []).map((detalle) => {
        const variante = detalle.product_variant ?? detalle.productVariant
        const nombre = variante?.product?.name ?? variante?.sku ?? '-'
        const presentacion = presentacionVariante(variante)

        return presentacion ? `${nombre} - ${presentacion}` : nombre
    })
}

const anular = (id) => {
    if (!confirm(
        '¿Está seguro de anular esta venta?\n\nEl stock asociado será revertido.'
    )) {
        return
    }

    router.delete(
        route('ventas.destroy', id),
        { preserveScroll: true }
    )
}

</script>


<template>

    <Head title="Ventas" />

    <AppLayout>

        <PageHeader
            title="Ventas"
            subtitle="Registro y control de ventas realizadas"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </template>

            <template #actions>
                <Link :href="route('ventas.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva venta
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total ventas" :value="resumen.total" hint="Ventas registradas" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9a1.5 1.5 0 011.5 1.5v13.5a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5V5.25a1.5 1.5 0 011.5-1.5zM9 7.5h6M9 11.25h6M9 15h3" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Monto total" :value="'Bs. ' + formatoMoneda(resumen.montoTotal)" hint="Ventas activas" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Saldo pendiente" :value="'Bs. ' + formatoMoneda(resumen.saldoPendiente)" hint="Por cobrar en ventas activas" color="red">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="A crédito" :value="resumen.credito" hint="Ventas con pago diferido" color="amber">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de ventas</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ ventas.total }} registro{{ ventas.total === 1 ? '' : 's' }}
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="venta in ventas.data"
                :key="venta.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-60': venta.estado !== 'activo' }"
            >

                <td data-label="Productos" class="px-5 py-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p
                                v-for="(nombre, index) in nombresProductos(venta.detalles).slice(0, 2)"
                                :key="index"
                                class="font-semibold text-slate-800 leading-snug"
                            >
                                {{ nombre }}
                            </p>

                            <p
                                v-if="(venta.detalles?.length ?? 0) > 2"
                                class="text-xs text-slate-400"
                            >
                                +{{ venta.detalles.length - 2 }} más
                            </p>

                            <p
                                v-if="(venta.detalles?.length ?? 0) === 0"
                                class="text-slate-400 italic"
                            >
                                Sin productos
                            </p>

                            <p class="text-xs text-slate-400 mt-1">
                                {{ venta.numero }}
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

                <td data-label="Total" class="px-5 py-4">
                    <span class="font-semibold text-slate-800">
                        Bs. {{ formatoMoneda(venta.total) }}
                    </span>
                </td>

                <td data-label="Pago" class="px-5 py-4">
                    <Badge :color="venta.pago_tipo === 'credito' ? 'yellow' : 'green'" dot>
                        {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                    </Badge>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="venta.estado === 'activo' ? 'green' : 'red'" dot>
                        {{ venta.estado === 'activo' ? 'Activo' : 'Anulado' }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            :href="route('ventas.show', venta.id)"
                            title="Ver detalle"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </Link>

                        <Link
                            v-if="venta.estado === 'activo'"
                            :href="route('ventas.edit', venta.id)"
                            title="Editar venta"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            v-if="venta.estado === 'activo'"
                            type="button"
                            title="Anular venta"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                            @click="anular(venta.id)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="ventas.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No existen ventas registradas"
                        message="Aún no hay ventas en el sistema. Registra la primera venta para comenzar."
                    >
                        <template #action>
                            <Link :href="route('ventas.create')">
                                <Button>Registrar venta</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="ventas.links" />

    </AppLayout>

</template>
