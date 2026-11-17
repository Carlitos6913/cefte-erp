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
    variantes: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, activas: 0, inactivas: 0, stockBajo: 0 })
    },
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
    'Producto',
    'Empaque / Unidad',
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

        <PageHeader
            title="Variantes"
            subtitle="Presentaciones, empaques y stock de cada producto"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por producto, SKU o sabor..."
                />

                <Link :href="route('variantes.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva variante
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total variantes" :value="resumen.total" hint="Presentaciones registradas" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Activas" :value="resumen.activas" hint="Disponibles para operar" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Stock bajo" :value="resumen.stockBajo" hint="En o por debajo del mínimo" color="amber">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Inactivas" :value="resumen.inactivas" hint="Fuera de operación" color="slate">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de variantes</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ variantes.total }} registro{{ variantes.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="variante in variantes.data"
                :key="variante.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-50': !variante.is_active }"
            >

                <td data-label="Producto" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <ProductVisual
                            :name="variante.product?.name"
                            :category="variante.product?.category?.name"
                            :image="variante.product?.image_url"
                            class="w-11 h-11 rounded-xl shrink-0"
                        />
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ variante.product?.name ?? '—' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">
                                {{ variante.sku }}
                            </p>
                            <p v-if="variante.flavor" class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ variante.flavor }}
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Empaque / Unidad" class="px-5 py-4">
                    <p class="text-slate-600">{{ variante.packaging_type || '—' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ variante.unidad_base?.name ?? variante.unit_measure?.name }}
                        ({{ variante.unidad_base?.symbol ?? variante.unit_measure?.symbol }})
                    </p>
                </td>

                <td data-label="Precio" class="px-5 py-4">
                    <p class="font-semibold text-slate-700">
                        Bs. {{ Number(variante.unit_price).toFixed(2) }}
                    </p>
                    <p
                        v-if="variante.factor_empaque"
                        class="text-xs text-slate-400 mt-0.5"
                    >
                        Bs. {{ Number(variante.precio_por_empaque).toFixed(2) }}
                        por {{ variante.unidad_empaque?.name?.toLowerCase() }}
                    </p>
                </td>

                <td data-label="Stock" class="px-5 py-4">
                    <p
                        class="font-semibold"
                        :class="Number(variante.stock) <= Number(variante.minimum_stock)
                            ? 'text-amber-600'
                            : 'text-slate-700'"
                    >
                        {{ variante.stock }}
                    </p>
                    <p
                        v-if="variante.factor_empaque"
                        class="text-xs text-slate-400 mt-0.5"
                    >
                        = {{ variante.stock_en_empaque }}
                        {{ variante.unidad_empaque?.symbol }}
                    </p>
                    <p v-else class="text-xs text-slate-400 mt-0.5">
                        Mín. {{ variante.minimum_stock }}
                    </p>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="colorVariante(variante)" dot>
                        {{ estadoVariante(variante) }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            v-if="variante.is_active"
                            :href="route('variantes.edit', variante.id)"
                            title="Editar variante"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            type="button"
                            :title="variante.is_active ? 'Desactivar variante' : 'Activar variante'"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition"
                            :class="variante.is_active
                                ? 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                : 'text-emerald-600 bg-emerald-50 border-emerald-200 hover:bg-emerald-100'"
                            @click="toggleActivo(variante.id, variante.is_active)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="variantes.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron variantes"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Aún no hay variantes registradas. Crea la primera para comenzar.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                            <Link v-else :href="route('variantes.create')">
                                <Button>Registrar variante</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="variantes.links" />

    </AppLayout>

</template>
