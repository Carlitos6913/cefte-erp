<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
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

        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Categorías
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Organización de productos por categorías
                </p>
            </div>

            <div class="flex items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar categoría..."
                    class="w-64 rounded-lg border-slate-300
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

                <td class="p-4 font-medium text-slate-700">
                    {{ categoria.code }}
                </td>

                <td class="p-4 font-medium text-slate-800">
                    {{ categoria.name }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ categoria.description || 'Sin descripción' }}
                </td>

                <td class="p-4 text-slate-700">
                    {{ categoria.products_count }}
                </td>

                <td class="p-4">
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

            <tr v-if="categorias.data.length === 0">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen categorías registradas.
                </td>
            </tr>

        </Table>

        <div
            v-if="categorias.links && categorias.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template
                v-for="link in categorias.links"
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