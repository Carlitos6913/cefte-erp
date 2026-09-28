<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    ventasConDeuda: Object,
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
    'Nro. Venta',
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

        <div class="mb-6">

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                Control de pagos
            </h1>

            <p class="text-sm text-slate-500 mt-1">
                Ventas a crédito con saldo pendiente de cobro
            </p>

        </div>


        <div class="flex justify-end mb-4">

            <input
                v-model="buscar"
                type="text"
                placeholder="Buscar por nro. venta o cliente..."
                class="w-full sm:w-80 rounded-lg border-slate-300
                       focus:border-emerald-500
                       focus:ring-emerald-500"
            />

        </div>


        <Table :headers="headers">

            <tr
                v-for="venta in ventasConDeuda.data"
                :key="venta.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <td data-label="Nro. Venta" class="p-4 font-medium text-slate-700">
                    {{ venta.numero }}
                </td>

                <td data-label="Fecha" class="p-4 text-slate-600">
                    {{ formatoFecha(venta.fecha) }}
                </td>

                <td data-label="Cliente" class="p-4 text-slate-600">
                    {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                </td>

                <td data-label="Total" class="p-4 font-semibold text-slate-700">
                    Bs. {{ formatoMoneda(venta.total) }}
                </td>

                <td data-label="Saldo pendiente" class="p-4">
                    <span class="font-bold text-red-600">
                        Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                    </span>
                </td>

                <td class="p-4 table-actions">
                    <div class="flex gap-2">

                        <Link
                            :href="route('pagos.create', { venta_id: venta.id })"
                        >
                            <Button>
                                Registrar pago
                            </Button>
                        </Link>

                        <Link
                            :href="route('pagos.show', venta.id)"
                        >
                            <Button color="secondary">
                                Ver pagos
                            </Button>
                        </Link>

                    </div>
                </td>

            </tr>

            <tr v-if="ventasConDeuda.data.length === 0" class="table-empty">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen ventas con saldo pendiente.
                </td>
            </tr>

        </Table>


        <Pagination :links="ventasConDeuda.links" />

    </AppLayout>

</template>
