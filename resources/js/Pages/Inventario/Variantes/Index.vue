<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

import { ref, watch } from 'vue'


const props = defineProps({
    variantes: Object,
    filters: Object
})


const buscar = ref(props.filters.buscar ?? '')


watch(buscar, (value) => {

    router.get(
        route('variantes.index'),
        {
            buscar: value
        },
        {
            preserveState: true,
            replace: true
        }
    )

})

const headers = [
    'SKU',
    'Producto',
    'Sabor',
    'Empaque',
    'Unidad',
    'Precio',
    'Stock',
    'Estado',
    'Acciones'
]


const estadoVariante = (variante) => {

    if (!variante.is_active) {
        return 'Desactivada'
    }

    if (Number(variante.stock) <= 0) {
        return 'Agotada'
    }

    if (Number(variante.stock) <= Number(variante.minimum_stock)) {
        return 'Stock bajo'
    }

    return 'Disponible'
}


const colorVariante = (variante) => {

    if (!variante.is_active) {
        return 'gray'
    }

    if (Number(variante.stock) <= 0) {
        return 'red'
    }

    if (Number(variante.stock) <= Number(variante.minimum_stock)) {
        return 'yellow'
    }

    return 'green'
}


const toggleActivo = (id, activo) => {
    if (activo) {
        if (!confirm('¿Está seguro de desactivar esta variante?')) {
            return
        }
        router.delete(
            route('variantes.destroy', id),
            { preserveScroll: true }
        )
    } else {
        if (!confirm('¿Está seguro de activar esta variante?')) {
            return
        }
        router.post(
            route('variantes.activar', id),
            {},
            { preserveScroll: true }
        )
    }
}

</script>


<template>

    <Head title="Variantes" />

    <AppLayout>

        <!-- Encabezado -->

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Variantes
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Variantes de producto con unidad de medida y stock
                </p>

            </div>


            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <!-- Buscador -->

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar variante..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />


                <!-- Nueva variante -->

                <Link :href="route('variantes.create')">

                    <Button>
                        Nueva variante
                    </Button>

                </Link>

            </div>

        </div>


        <!-- Tabla -->

        <Table :headers="headers">

            <tr
                v-for="variante in variantes.data"
                :key="variante.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
                :class="{ 'opacity-40': !variante.is_active }"
            >

                <!-- SKU -->

                <td data-label="SKU" class="p-4 font-medium text-slate-700">
                    {{ variante.sku }}
                </td>


                <!-- Producto -->

                <td data-label="Producto" class="p-4">

                    <div class="font-medium text-slate-800">
                        {{ variante.product?.name }}
                    </div>

                    <div class="text-xs text-slate-500 mt-1">
                        {{ variante.product?.code }}
                    </div>

                </td>


                <!-- Sabor -->

                <td data-label="Sabor" class="p-4 text-slate-600">
                    {{ variante.flavor || '-' }}
                </td>


                <!-- Empaque -->

                <td data-label="Empaque" class="p-4 text-slate-600">
                    {{ variante.packaging_type || '-' }}
                </td>


                <!-- Unidad -->

                <td data-label="Unidad" class="p-4 text-slate-600">
                    {{ variante.unit_measure?.name }}
                    ({{ variante.unit_measure?.symbol }})
                </td>


                <!-- Precio -->

                <td data-label="Precio" class="p-4 text-slate-700">
                    Bs. {{ Number(variante.unit_price).toFixed(2) }}
                </td>


                <!-- Stock -->

                <td data-label="Stock" class="p-4">

                    <span class="font-semibold text-slate-700">
                        {{ variante.stock }}
                    </span>

                </td>


                <!-- Estado -->

                <td data-label="Estado" class="p-4">

                    <Badge :color="colorVariante(variante)">
                        {{ estadoVariante(variante) }}
                    </Badge>

                </td>


                <!-- Acciones -->

                <td class="p-4 table-actions">

                    <div class="flex gap-2">

                        <Link
                            v-if="variante.is_active"
                            :href="route('variantes.edit', variante.id)"
                        >

                            <Button color="info">
                                Editar
                            </Button>

                        </Link>


                        <Button
                            :color="variante.is_active ? 'danger' : 'success'"
                            @click="toggleActivo(variante.id, variante.is_active)"
                        >
                            {{ variante.is_active ? 'Desactivar' : 'Activar' }}
                        </Button>

                    </div>

                </td>

            </tr>


            <!-- Sin resultados -->

            <tr v-if="variantes.data.length === 0" class="table-empty">

                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No se encontraron variantes.
                </td>

            </tr>

        </Table>


        <Pagination :links="variantes.links" />

    </AppLayout>

</template>