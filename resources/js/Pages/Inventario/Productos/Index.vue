<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import ProductVisual from '@/Components/ProductVisual.vue'


const props = defineProps({
    productos: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, activos: 0, inactivos: 0, variantes: 0 })
    },
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

        <PageHeader
            title="Productos"
            subtitle="Catálogo de productos registrados en el inventario"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M12 11.25V21m0-9.75L3.75 7.5m8.25 3.75l8.25-3.75M3.75 7.5l8.25-3.75L20.25 7.5" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por nombre o código..."
                />

                <Link :href="route('productos.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo producto
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total productos" :value="resumen.total" hint="Registrados en el catálogo" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M12 11.25V21m0-9.75L3.75 7.5m8.25 3.75l8.25-3.75M3.75 7.5l8.25-3.75L20.25 7.5" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Activos" :value="resumen.activos" hint="Disponibles en el catálogo" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Variantes" :value="resumen.variantes" hint="Presentaciones registradas" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Inactivos" :value="resumen.inactivos" hint="Fuera del catálogo" color="slate">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de productos</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ productos.total }} registro{{ productos.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="producto in productos.data"
                :key="producto.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-50': !producto.is_active }"
            >

                <td data-label="Producto" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <ProductVisual
                            :name="producto.name"
                            :category="producto.category?.name"
                            :image="producto.image_url"
                            class="w-11 h-11 rounded-xl shrink-0"
                        />
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ producto.name }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">
                                {{ producto.code }}
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Categoría" class="px-5 py-4">
                    <Badge v-if="producto.category" color="indigo" dot>
                        {{ producto.category.name }}
                    </Badge>
                    <Badge v-else color="gray">
                        Sin categoría
                    </Badge>
                </td>

                <td data-label="Variantes" class="px-5 py-4">
                    <Badge :color="producto.product_variants_count > 0 ? 'blue' : 'gray'" dot>
                        {{ producto.product_variants_count }}
                        {{ producto.product_variants_count === 1 ? 'variante' : 'variantes' }}
                    </Badge>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="producto.is_active ? 'green' : 'red'" dot>
                        {{ producto.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            v-if="producto.is_active"
                            :href="route('productos.edit', producto.id)"
                            title="Editar producto"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            type="button"
                            :title="producto.is_active ? 'Desactivar producto' : 'Activar producto'"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition"
                            :class="producto.is_active
                                ? 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                : 'text-emerald-600 bg-emerald-50 border-emerald-200 hover:bg-emerald-100'"
                            @click="toggleActivo(producto.id, producto.is_active)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="productos.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron productos"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Aún no hay productos registrados. Crea el primero para comenzar.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                            <Link v-else :href="route('productos.create')">
                                <Button>Registrar producto</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="productos.links" />

    </AppLayout>

</template>
