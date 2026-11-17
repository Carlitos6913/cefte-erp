<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import ProductVisual from '@/Components/ProductVisual.vue'

const props = defineProps({
    salidas: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, esteMes: 0, hoy: 0, anuladas: 0 })
    }
})

const headers = ['Productos', 'Fecha', 'Motivo', 'Registrado por', 'Estado / Detalle', 'Acciones']

const productosSalida = (detalles = []) => {
    const vistos = new Set()
    const productos = []

    for (const detalle of (detalles ?? [])) {
        const variante = detalle.product_variant ?? detalle.productVariant
        const producto = variante?.product
        const clave = producto?.id ?? variante?.sku ?? detalle.id

        if (vistos.has(clave)) continue

        vistos.add(clave)

        productos.push({
            key: clave,
            nombre: producto?.name ?? variante?.sku ?? '-',
            sabor: variante?.flavor ?? null,
            imagen: producto?.image_url ?? null,
            categoria: producto?.category?.name ?? null,
        })
    }

    return productos
}

const filas = computed(() => {
    return (props.salidas?.data ?? []).map((salida) => ({
        salida,
        productos: productosSalida(salida.detalles),
    }))
})

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => Number(valor).toFixed(2)

const motivoLabel = (motivo) => {
    const map = { venta: 'Despacho para venta', dano: 'Daño', vencimiento: 'Vencimiento', degusto: 'Degusto/Muestra', donacion: 'Donación', ajuste: 'Ajuste', otro: 'Otro' }
    return map[motivo] ?? motivo
}

const motivoColor = (motivo) => {
    const map = { venta: 'green', dano: 'red', vencimiento: 'orange', degusto: 'purple', donacion: 'blue', ajuste: 'gray', otro: 'gray' }
    return map[motivo] ?? 'gray'
}

const anular = (id) => {
    if (!confirm('¿Está seguro de anular esta salida?\n\nEl stock asociado será revertido.')) return
    router.delete(route('salidas.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <Head title="Salidas" />

    <AppLayout>
        <PageHeader
            title="Salidas"
            subtitle="Registro y control de salidas de productos del almacén"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
            </template>

            <template #actions>
                <Link :href="route('salidas.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva salida
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total salidas" :value="resumen.total" hint="Registradas en el sistema" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Este mes" :value="resumen.esteMes" hint="Salidas del mes actual" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Hoy" :value="resumen.hoy" hint="Salidas registradas hoy" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Anuladas" :value="resumen.anuladas" hint="Salidas revertidas" color="red">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de salidas</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ salidas.total }} registro{{ salidas.total === 1 ? '' : 's' }}
                </p>
            </div>
        </div>

        <Table :headers="headers">
            <tr
                v-for="fila in filas"
                :key="fila.salida.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
            >

                <td data-label="Productos" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex shrink-0 -space-x-2">
                            <ProductVisual
                                v-for="producto in fila.productos.slice(0, 3)"
                                :key="producto.key"
                                :name="producto.nombre"
                                :category="producto.categoria"
                                :image="producto.imagen"
                                class="h-9 w-9 rounded-lg ring-2 ring-white"
                            />
                        </div>
                        <div class="min-w-0">
                            <div
                                v-for="producto in fila.productos.slice(0, 2)"
                                :key="producto.key"
                                class="truncate text-sm font-medium text-slate-700"
                            >
                                {{ producto.nombre }}
                                <span v-if="producto.sabor" class="font-normal text-slate-400">
                                    · {{ producto.sabor }}
                                </span>
                            </div>
                            <div v-if="fila.productos.length > 2" class="text-xs text-slate-400">
                                +{{ fila.productos.length - 2 }} más
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 font-mono">
                                {{ fila.salida.numero }}
                            </div>
                        </div>
                    </div>
                </td>

                <td data-label="Fecha" class="px-5 py-4 text-slate-600">
                    {{ formatoFecha(fila.salida.fecha) }}
                </td>

                <td data-label="Motivo" class="px-5 py-4">
                    <Badge :color="motivoColor(fila.salida.motivo)" dot>
                        {{ motivoLabel(fila.salida.motivo) }}
                    </Badge>
                </td>

                <td data-label="Registrado por" class="px-5 py-4 text-slate-600">
                    {{ fila.salida.usuario?.name || '-' }}
                </td>

                <td data-label="Estado / Detalle" class="px-5 py-4">
                    <Badge :color="fila.salida.estado === 'activo' ? 'green' : 'red'" dot>
                        {{ fila.salida.estado === 'activo' ? 'Activa' : 'Anulada' }}
                    </Badge>
                    <p class="text-xs text-slate-400 mt-1">
                        Total Bs. {{ formatoMoneda(fila.salida.total) }}
                    </p>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            :href="route('salidas.show', fila.salida.id)"
                            title="Ver detalle"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-slate-100 hover:text-slate-700 hover:border-slate-300 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </Link>

                        <Link
                            v-if="fila.salida.estado === 'activo'"
                            :href="route('salidas.edit', fila.salida.id)"
                            title="Editar salida"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            v-if="fila.salida.estado === 'activo'"
                            type="button"
                            title="Anular salida"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                            @click="anular(fila.salida.id)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="filas.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No existen salidas registradas"
                        message="Aún no hay salidas en el almacén. Registra la primera para comenzar."
                    >
                        <template #action>
                            <Link :href="route('salidas.create')">
                                <Button>Registrar salida</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>
        </Table>

        <Pagination :links="salidas.links" />
    </AppLayout>
</template>
