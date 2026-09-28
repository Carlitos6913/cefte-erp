<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


defineProps({
    ingresos: Object
})


const headers = [
    'Producto',
    'Fecha',
    'Proveedor',
    'Cant. Ingresado',
    'Total',
    'Registrado por',
    'Estado',
    'Acciones'
]


const formatoFecha = (fecha) => {

    if (!fecha) {
        return '-'
    }

    return new Date(fecha).toLocaleDateString('es-BO')
}


const formatoMoneda = (valor) => {

    return Number(valor).toFixed(2)

}

const anular = (id) => {

    if (!confirm(
        '¿Está seguro de anular este ingreso?\n\nEl stock asociado será revertido.'
    )) {
        return
    }

    router.delete(
        route('ingresos.destroy', id),
        {
            preserveScroll: true,
        }
    )
}


</script>


<template>

    <Head title="Ingresos" />

    <AppLayout>

        <!-- Encabezado -->

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Ingresos
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Registro y control de entradas de productos al almacén
                </p>

            </div>


            <Link :href="route('ingresos.create')">

                <Button>
                    Nuevo ingreso
                </Button>

            </Link>

        </div>


        <!-- Tabla -->

        <Table :headers="headers">

            <tr
                v-for="ingreso in ingresos.data"
                :key="ingreso.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <!-- Producto -->

                <td data-label="Producto" class="p-4">
                    <div
                        v-for="detalle in ingreso.detalles"
                        :key="detalle.id"
                        class="font-medium text-slate-700"
                    >
                        {{ detalle.product_variant?.product?.name ?? detalle.product_variant?.sku ?? 'Producto no disponible' }}
                    </div>

                    <div class="text-xs text-slate-400 mt-1">
                        {{ ingreso.numero }}
                    </div>
                </td>


                <!-- Fecha -->

                <td data-label="Fecha" class="p-4 text-slate-600">

                    {{ formatoFecha(ingreso.fecha) }}

                </td>


                <!-- Proveedor -->

                <td data-label="Proveedor" class="p-4 text-slate-600">

                    {{ ingreso.proveedor || 'Sin proveedor' }}

                </td>


                <!-- Detalles -->

                <td data-label="Cant. Ingresado" class="p-4 text-slate-600">

                    {{ ingreso.detalles_count }}

                </td>


                <!-- Total -->

                <td data-label="Total" class="p-4">

                    <span class="font-semibold text-slate-700">
                        Bs. {{ formatoMoneda(ingreso.total) }}
                    </span>

                </td>


                <!-- Usuario -->

                <td data-label="Registrado por" class="p-4 text-slate-600">

                    {{ ingreso.usuario?.name || '-' }}

                </td>

                <td data-label="Estado" class="p-4">

                    <Badge
                        :color="ingreso.estado === 'activo'
                            ? 'green'
                            : 'red'"
                    >
                        {{ ingreso.estado === 'activo'
                            ? 'Activo'
                            : 'Anulado'
                        }}
                    </Badge>

                </td>


                <!-- Acciones -->

                <td class="p-4 table-actions">
    <div class="flex gap-2">

        <!-- Ver -->
        <Link
            :href="route('ingresos.show', ingreso.id)"
        >
            <Button color="secondary">
                Ver
            </Button>
        </Link>


        <!-- Editar -->
        <Link
            v-if="ingreso.estado === 'activo'"
            :href="route('ingresos.edit', ingreso.id)"
        >
            <Button color="info">
                Editar
            </Button>
        </Link>


        <!-- Anular -->
        <Button
            v-if="ingreso.estado === 'activo'"
            color="danger"
            @click="anular(ingreso.id)"
        >
            Anular
        </Button>

    </div>
</td>

            </tr>


            <!-- Sin resultados -->

            <tr v-if="ingresos.data.length === 0" class="table-empty">

                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen ingresos registrados.
                </td>

            </tr>

        </Table>


        <Pagination :links="ingresos.links" />

    </AppLayout>

</template>
