<script setup>

import { Head, Link } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


const props = defineProps({
    venta: Object,
})


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}

const totalPagado = () => {
    return props.venta.pagos.reduce(
        (sum, pago) => sum + Number(pago.monto),
        0
    )
}

const metodoLabel = (metodo) => {
    const map = {
        efectivo: 'Efectivo',
        transferencia: 'Transferencia',
        qr: 'QR',
        otro: 'Otro',
    }
    return map[metodo] ?? metodo
}

</script>


<template>

    <Head :title="`Pagos - ${venta.numero}`" />

    <AppLayout>

        <div class="max-w-5xl mx-auto">

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <Link
                            :href="route('pagos.index')"
                            class="text-slate-500 hover:text-slate-700"
                        >
                            ←
                        </Link>

                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                            Pagos - {{ venta.numero }}
                        </h1>

                    </div>

                    <p class="text-sm text-slate-500 mt-1">
                        Historial de pagos recibidos
                    </p>

                </div>

                <Link :href="route('pagos.index')">
                    <Button color="secondary">
                        Volver
                    </Button>
                </Link>

            </div>


            <!-- Resumen -->

            <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

                    <div>
                        <p class="text-sm text-slate-500">Total venta</p>
                        <p class="font-bold text-slate-800">
                            Bs. {{ formatoMoneda(venta.total) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Total pagado</p>
                        <p class="font-bold text-emerald-600">
                            Bs. {{ formatoMoneda(totalPagado()) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Saldo pendiente</p>
                        <p class="font-bold"
                           :class="venta.saldo_pendiente > 0 ? 'text-red-600' : 'text-emerald-600'"
                        >
                            Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Cliente</p>
                        <p class="font-semibold text-slate-800">
                            {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Tipo de pago</p>
                        <Badge :color="venta.pago_tipo === 'credito' ? 'yellow' : 'green'">
                            {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                        </Badge>
                    </div>

                </div>

            </div>


            <!-- Pagos registrados -->

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

                <div class="px-6 py-4 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-800">
                        Pagos registrados ({{ venta.pagos.length }})
                    </h2>

                </div>


                <table class="w-full table-responsive">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                Fecha
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                Monto
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                Método
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                Registrado por
                            </th>

                            <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                Observaciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr
                            v-for="pago in venta.pagos"
                            :key="pago.id"
                            class="border-t border-slate-200"
                        >

                            <td data-label="Fecha" class="p-4 text-slate-600">
                                {{ formatoFecha(pago.fecha) }}
                            </td>

                            <td data-label="Monto" class="p-4 font-semibold text-emerald-600">
                                Bs. {{ formatoMoneda(pago.monto) }}
                            </td>

                            <td data-label="Método" class="p-4 text-slate-600">
                                {{ metodoLabel(pago.metodo) }}
                            </td>

                            <td data-label="Registrado por" class="p-4 text-slate-600">
                                {{ pago.usuario?.name ?? '-' }}
                            </td>

                            <td data-label="Observaciones" class="p-4 text-slate-500 text-sm">
                                {{ pago.observaciones || '-' }}
                            </td>

                        </tr>

                        <tr v-if="venta.pagos.length === 0" class="table-empty">

                            <td
                                colspan="5"
                                class="p-8 text-center text-slate-500"
                            >
                                No se han registrado pagos para esta venta.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </AppLayout>

</template>
