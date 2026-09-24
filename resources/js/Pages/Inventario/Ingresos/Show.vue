<script setup>

import { Head, Link } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    ingreso: Object
})


const formatoFecha = (fecha) => {

    if (!fecha) {
        return '-'
    }

    return new Date(fecha).toLocaleDateString('es-BO')
}


const formatoMoneda = (valor) => {

    return Number(valor).toFixed(2)

}

</script>


<template>

    <Head :title="`Ingreso ${ingreso.numero}`" />

    <AppLayout>

        <div class="max-w-6xl mx-auto">

            <!-- Encabezado -->

            <div class="flex justify-between items-start mb-6">

                <div>

                    <div class="flex items-center gap-3">

                        <Link
                            :href="route('ingresos.index')"
                            class="text-slate-500 hover:text-slate-700"
                        >
                            ←
                        </Link>

                        <h1 class="text-3xl font-bold text-slate-800">
                            {{ ingreso.numero }}
                        </h1>

                    </div>

                    <p class="text-sm text-slate-500 mt-1">
                        Detalle del ingreso registrado
                    </p>

                </div>


                <Link :href="route('ingresos.index')">

                    <Button color="secondary">
                        Volver
                    </Button>

                </Link>

            </div>
            <div
                class="bg-white rounded-xl border border-slate-200
                       shadow-sm p-6 mb-6"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div>

                        <p class="text-sm text-slate-500">
                            Número
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            {{ ingreso.numero }}
                        </p>
                    </div>
                    <div>

                        <p class="text-sm text-slate-500">
                            Fecha
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            {{ formatoFecha(ingreso.fecha) }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-slate-500">
                            Proveedor
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            {{ ingreso.proveedor || 'Sin proveedor' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-sm text-slate-500">
                            Registrado por
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            {{ ingreso.usuario?.name || '-' }}
                        </p>

                    </div>

                </div>


                <div
                    v-if="ingreso.observaciones"
                    class="mt-6 pt-6 border-t border-slate-200"
                >

                    <p class="text-sm text-slate-500">
                        Observaciones
                    </p>

                    <p class="text-slate-700 mt-1">
                        {{ ingreso.observaciones }}
                    </p>

                </div>

            </div>


            <!-- Detalles -->

            <div
                class="bg-white rounded-xl border border-slate-200
                       shadow-sm overflow-hidden"
            >

                <div class="px-6 py-4 border-b border-slate-200">

                    <h2 class="text-lg font-semibold text-slate-800">
                        Productos ingresados
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
                                v-for="detalle in ingreso.detalles"
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


                            <tr v-if="ingreso.detalles.length === 0">

                                <td
                                    colspan="6"
                                    class="p-8 text-center text-slate-500"
                                >
                                    Este ingreso no tiene productos registrados.
                                </td>

                            </tr>

                        </tbody>


                        <tfoot>

                            <tr class="border-t-2 border-slate-200">

                                <td
                                    colspan="5"
                                    class="p-5 text-right font-semibold text-slate-600"
                                >
                                    Total
                                </td>

                                <td
                                    class="p-5 text-right text-xl
                                           font-bold text-slate-800"
                                >
                                    Bs. {{ formatoMoneda(ingreso.total) }}
                                </td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>

    </AppLayout>

</template>
