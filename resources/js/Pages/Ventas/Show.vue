<script setup>

import { Head, Link } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


const props = defineProps({
    venta: Object
})


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}

const totalPagado = () => {
    if (!props.venta.pagos) return 0
    return props.venta.pagos.reduce(
        (sum, pago) => sum + Number(pago.monto),
        0
    )
}

</script>


<template>

    <Head :title="`Venta ${venta.numero}`" />

    <AppLayout>

        <div class="max-w-6xl mx-auto">

            <div class="flex justify-between items-start mb-6">

                <div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('ventas.index')"
                            class="text-slate-500 hover:text-slate-700"
                        >
                            ←
                        </Link>
                        <h1 class="text-3xl font-bold text-slate-800">
                            {{ venta.numero }}
                        </h1>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">
                        Detalle de la venta registrada
                    </p>
                </div>

                <Link :href="route('ventas.index')">
                    <Button color="secondary">
                        Volver
                    </Button>
                </Link>

            </div>


            <!-- Información general -->

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div>
                        <p class="text-sm text-slate-500">Número</p>
                        <p class="font-semibold text-slate-800 mt-1">
                            {{ venta.numero }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Fecha</p>
                        <p class="font-semibold text-slate-800 mt-1">
                            {{ formatoFecha(venta.fecha) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Cliente</p>
                        <p class="font-semibold text-slate-800 mt-1">
                            {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Registrado por</p>
                        <p class="font-semibold text-slate-800 mt-1">
                            {{ venta.usuario?.name || '-' }}
                        </p>
                    </div>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-slate-200">

                    <div>
                        <p class="text-sm text-slate-500">Tipo de pago</p>
                        <Badge
                            :color="venta.pago_tipo === 'credito' ? 'yellow' : 'green'"
                            class="mt-1"
                        >
                            {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                        </Badge>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Total</p>
                        <p class="text-xl font-bold text-slate-800 mt-1">
                            Bs. {{ formatoMoneda(venta.total) }}
                        </p>
                    </div>

                    <div v-if="venta.pago_tipo === 'credito'">
                        <p class="text-sm text-slate-500">Saldo pendiente</p>
                        <p class="text-xl font-bold mt-1"
                           :class="venta.saldo_pendiente > 0 ? 'text-red-600' : 'text-emerald-600'"
                        >
                            Bs. {{ formatoMoneda(venta.saldo_pendiente) }}
                        </p>
                    </div>

                </div>


                <div
                    v-if="venta.observaciones"
                    class="mt-6 pt-6 border-t border-slate-200"
                >
                    <p class="text-sm text-slate-500">Observaciones</p>
                    <p class="text-slate-700 mt-1">{{ venta.observaciones }}</p>
                </div>

            </div>


            <!-- Detalles -->

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">

                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Productos vendidos
                    </h2>
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-slate-50">

                            <tr>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                    Código
                                </th>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                    Producto
                                </th>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">
                                    Unidad
                                </th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">
                                    Cantidad
                                </th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">
                                    Precio unitario
                                </th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">
                                    Subtotal
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="detalle in venta.detalles"
                                :key="detalle.id"
                                class="border-t border-slate-200"
                            >
                                <td class="p-4 font-medium text-slate-700">
                                    {{ detalle.product_variant?.sku }}
                                </td>
                                <td class="p-4 text-slate-700">
                                    {{ detalle.product_variant?.product?.name }}
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ detalle.product_variant?.unit_measure?.name }}
                                </td>
                                <td class="p-4 text-right font-semibold">
                                    {{ detalle.cantidad }}
                                </td>
                                <td class="p-4 text-right">
                                    Bs. {{ formatoMoneda(detalle.precio_unitario) }}
                                </td>
                                <td class="p-4 text-right font-semibold text-slate-800">
                                    Bs. {{ formatoMoneda(detalle.subtotal) }}
                                </td>
                            </tr>

                            <tr v-if="venta.detalles.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    Esta venta no tiene productos registrados.
                                </td>
                            </tr>

                        </tbody>

                        <tfoot>

                            <tr class="border-t-2 border-slate-200">
                                <td colspan="5" class="p-5 text-right font-semibold text-slate-600">
                                    Total
                                </td>
                                <td class="p-5 text-right text-xl font-bold text-slate-800">
                                    Bs. {{ formatoMoneda(venta.total) }}
                                </td>
                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>


            <!-- Pagos (si es crédito) -->

            <div
                v-if="venta.pago_tipo === 'credito' && venta.pagos && venta.pagos.length > 0"
                class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"
            >

                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Pagos recibidos
                    </h2>
                </div>

                <table class="w-full">

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
                        </tr>

                    </thead>

                    <tbody>

                        <tr
                            v-for="pago in venta.pagos"
                            :key="pago.id"
                            class="border-t border-slate-200"
                        >
                            <td class="p-4 text-slate-600">
                                {{ formatoFecha(pago.fecha) }}
                            </td>
                            <td class="p-4 font-semibold text-emerald-600">
                                Bs. {{ formatoMoneda(pago.monto) }}
                            </td>
                            <td class="p-4 text-slate-600">
                                {{ pago.metodo }}
                            </td>
                            <td class="p-4 text-slate-600">
                                {{ pago.usuario?.name ?? '-' }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </AppLayout>

</template>
