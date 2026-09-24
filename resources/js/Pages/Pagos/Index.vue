<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
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

            <h1 class="text-3xl font-bold text-slate-800">
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
                class="w-80 rounded-lg border-slate-300
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

                <td class="p-4 font-medium text-slate-700">
                    {{ venta.numero }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ formatoFecha(venta.fecha) }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                </td>

                <td class="p-4 font-semibold text-slate-700">
                    Bs. {{ formatoMoneda(venta.total) }}
                </td>

                <td class="p-4">
                    <span class="font-bold text-red-600">
                        Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                    </span>
                </td>

                <td class="p-4">
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

            <tr v-if="ventasConDeuda.data.length === 0">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen ventas con saldo pendiente.
                </td>
            </tr>

        </Table>


        <div
            v-if="ventasConDeuda.links && ventasConDeuda.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template
                v-for="link in ventasConDeuda.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
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
