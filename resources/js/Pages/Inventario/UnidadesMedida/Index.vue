<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    unidades: Object,
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('unidades.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})

const headers = [
    'Nombre',
    'Símbolo',
    'Variantes',
    'Acciones'
]

const eliminar = (id) => {
    if (!confirm('¿Está seguro de eliminar esta unidad de medida?')) {
        return
    }
    router.delete(
        route('unidades.destroy', id),
        { preserveScroll: true }
    )
}
</script>

<template>
    <Head title="Unidades de medida" />

    <AppLayout>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Unidades de medida
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Catálogo de unidades utilizadas en las variantes
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar unidad..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />

                <Link :href="route('unidades.create')">
                    <Button>
                        Nueva unidad
                    </Button>
                </Link>

            </div>

        </div>

        <Table :headers="headers">

            <tr
                v-for="unidad in unidades.data"
                :key="unidad.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <td data-label="Nombre" class="p-4 font-medium text-slate-800">
                    {{ unidad.name }}
                </td>

                <td data-label="Símbolo" class="p-4 text-slate-600">
                    {{ unidad.symbol }}
                </td>

                <td data-label="Variantes" class="p-4 text-slate-700">
                    {{ unidad.product_variants_count }}
                </td>

                <td class="p-4 table-actions">
                    <div class="flex gap-2">

                        <Link
                            :href="route('unidades.edit', unidad.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Button
                            color="danger"
                            @click="eliminar(unidad.id)"
                        >
                            Eliminar
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="unidades.data.length === 0" class="table-empty">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen unidades de medida registradas.
                </td>
            </tr>

        </Table>

        <Pagination :links="unidades.links" />

    </AppLayout>
</template>