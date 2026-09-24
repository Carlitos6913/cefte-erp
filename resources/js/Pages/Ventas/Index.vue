<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


defineProps({
    ventas: Object
})


const headers = [
    'Nro. Venta',
    'Fecha',
    'Cliente',
    'Productos',
    'Total',
    'Pago',
    'Saldo',
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

        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Ventas
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Registro y control de ventas realizadas
                </p>
            </div>

            <Link :href="route('ventas.create')">
                <Button>
                    Nueva venta
                </Button>
            </Link>

        </div>


        <Table :headers="headers">

            <tr
                v-for="venta in ventas.data"
                :key="venta.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <td class="p-4">
                    <div class="font-medium text-slate-700">
                        {{ venta.numero }}
                    </div>
                </td>

                <td class="p-4 text-slate-600">
                    {{ formatoFecha(venta.fecha) }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ venta.detalles_count }}
                </td>

                <td class="p-4">
                    <span class="font-semibold text-slate-700">
                        Bs. {{ formatoMoneda(venta.total) }}
                    </span>
                </td>

                <td class="p-4">
                    <Badge :color="venta.pago_tipo === 'credito' ? 'yellow' : 'green'">
                        {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                    </Badge>
                </td>

                <td class="p-4">
                    <span
                        v-if="venta.pago_tipo === 'credito'"
                        class="font-semibold"
                        :class="venta.saldo_pendiente > 0 ? 'text-red-600' : 'text-emerald-600'"
                    >
                        Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                    </span>
                    <span v-else class="text-slate-400">-</span>
                </td>

                <td class="p-4">
                    <Badge
                        :color="venta.estado === 'activo' ? 'green' : 'red'"
                    >
                        {{ venta.estado === 'activo' ? 'Activo' : 'Anulado' }}
                    </Badge>
                </td>

                <td class="p-4">
                    <div class="flex gap-2">

                        <Link :href="route('ventas.show', venta.id)">
                            <Button color="secondary">
                                Ver
                            </Button>
                        </Link>

                        <Link
                            v-if="venta.estado === 'activo'"
                            :href="route('ventas.edit', venta.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Button
                            v-if="venta.estado === 'activo'"
                            color="danger"
                            @click="anular(venta.id)"
                        >
                            Anular
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="ventas.data.length === 0">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen ventas registradas.
                </td>
            </tr>

        </Table>


        <div
            v-if="ventas.links && ventas.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template
                v-for="link in ventas.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                    class="px-3 py-2 rounded-lg border text-sm transition"
                    :class="link.active
                        ? 'bg-emerald-600 text-white border-emerald-600'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                />
                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-2 rounded-lg border border-slate-200
                           text-sm text-slate-300 bg-slate-50"
                />
            </template>
        </div>

    </AppLayout>

</template>
