<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    categorias: Object,
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('categorias.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})

const headers = [
    'Código',
    'Nombre',
    'Descripción',
    'Productos',
    'Acciones'
]

const eliminar = (id) => {
    if (!confirm('¿Está seguro de eliminar esta categoría?')) {
        return
    }
    router.delete(
        route('categorias.destroy', id),
        { preserveScroll: true }
    )
}
</script>

<template>
    <Head title="Categorías" />

    <AppLayout>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Categorías
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Organización de productos por categorías
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar categoría..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />

                <Link :href="route('categorias.create')">
                    <Button>
                        Nueva categoría
                    </Button>
                </Link>

            </div>

        </div>

        <Table :headers="headers">

            <tr
                v-for="categoria in categorias.data"
                :key="categoria.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <td data-label="Código" class="p-4 font-medium text-slate-700">
                    {{ categoria.code }}
                </td>

                <td data-label="Nombre" class="p-4 font-medium text-slate-800">
                    {{ categoria.name }}
                </td>

                <td data-label="Descripción" class="p-4 text-slate-600">
                    {{ categoria.description || 'Sin descripción' }}
                </td>

                <td data-label="Productos" class="p-4 text-slate-700">
                    {{ categoria.products_count }}
                </td>

                <td class="p-4 table-actions">
                    <div class="flex gap-2">

                        <Link
                            :href="route('categorias.edit', categoria.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Button
                            color="danger"
                            @click="eliminar(categoria.id)"
                        >
                            Eliminar
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="categorias.data.length === 0" class="table-empty">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen categorías registradas.
                </td>
            </tr>

        </Table>

        <Pagination :links="categorias.links" />

    </AppLayout>
</template>