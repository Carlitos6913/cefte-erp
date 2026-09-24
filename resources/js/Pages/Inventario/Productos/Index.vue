<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

import { ref, watch } from 'vue'


const props = defineProps({
    productos: Object,
    filters: Object
})


const buscar = ref(props.filters.buscar ?? '')


watch(buscar, (value) => {

    router.get(
        route('productos.index'),
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
    'Código',
    'Producto',
    'Categoría',
    'Variantes',
    'Estado',
    'Acciones'
]


const toggleActivo = (id, activo) => {
    if (activo) {
        if (!confirm('¿Está seguro de desactivar este producto?')) {
            return
        }
        router.delete(
            route('productos.destroy', id),
            { preserveScroll: true }
        )
    } else {
        if (!confirm('¿Está seguro de activar este producto?')) {
            return
        }
        router.post(
            route('productos.activar', id),
            {},
            { preserveScroll: true }
        )
    }
}

</script>


<template>

    <Head title="Productos" />

    <AppLayout>

        <!-- Encabezado -->

        <div class="flex justify-between items-center mb-6">

            <div>

                <h1 class="text-3xl font-bold text-slate-800">
                    Productos
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Gestión de productos
                </p>

            </div>


            <div class="flex items-center gap-3">

                <!-- Buscador -->

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar producto..."
                    class="w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />


                <!-- Nuevo producto -->

                <Link :href="route('productos.create')">

                    <Button>
                        Nuevo producto
                    </Button>

                </Link>

            </div>

        </div>


        <!-- Tabla -->

        <Table :headers="headers">

            <tr
                v-for="producto in productos.data"
                :key="producto.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
                :class="{ 'opacity-40': !producto.is_active }"
            >

                <!-- Código -->

                <td class="p-4 font-medium text-slate-700">
                    {{ producto.code }}
                </td>


                <!-- Producto -->

                <td class="p-4">

                    <div class="font-medium text-slate-800">
                        {{ producto.name }}
                    </div>

                    <div
                        v-if="producto.description"
                        class="text-xs text-slate-500 mt-1"
                    >
                        {{ producto.description }}
                    </div>

                </td>


                <td class="p-4">
                    <span
                        v-if="producto.category"
                        class="px-3 py-1 rounded-full text-xs font-medium
                            bg-emerald-100 text-emerald-700"
                    >
                        {{ producto.category.name }}
                    </span>

                    <span
                        v-else
                        class="text-sm text-slate-400"
                    >
                        Sin categoría
                    </span>
                </td>


                <!-- Variantes -->

                <td class="p-4 text-slate-700">
                    {{ producto.product_variants_count }}
                </td>


                <!-- Estado -->

                <td class="p-4">

                    <Badge :color="producto.is_active ? 'green' : 'red'">
                        {{ producto.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>

                </td>


                <!-- Acciones -->

                <td class="p-4">

                    <div class="flex gap-2">

                        <Link
                            v-if="producto.is_active"
                            :href="route('productos.edit', producto.id)"
                        >

                            <Button color="info">
                                Editar
                            </Button>

                        </Link>


                        <Button
                            :color="producto.is_active ? 'danger' : 'success'"
                            @click="toggleActivo(producto.id, producto.is_active)"
                        >
                            {{ producto.is_active ? 'Desactivar' : 'Activar' }}
                        </Button>

                    </div>

                </td>

            </tr>


            <!-- Sin resultados -->

            <tr v-if="productos.data.length === 0">

                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No se encontraron productos.
                </td>

            </tr>

        </Table>


        <!-- Paginación -->

        <div
            v-if="productos.links && productos.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >

            <template
                v-for="link in productos.links"
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