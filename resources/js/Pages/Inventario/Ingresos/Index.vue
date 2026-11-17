<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import ProductVisual from '@/Components/ProductVisual.vue'


const props = defineProps({
    ingresos: Object,
    productosStock: {
        type: Array,
        default: () => [],
    },
})


const buscarStock = ref('')
const categoriaActiva = ref('Todas')
const mostrarTodos = ref(false)


const categorias = computed(() => {

    const lista = props.productosStock
        .map((producto) => producto.categoria)
        .filter(Boolean)

    return ['Todas', ...new Set(lista)]

})


const productosFiltrados = computed(() => {

    const termino = buscarStock.value.trim().toLowerCase()

    return props.productosStock.filter((producto) => {

        const coincideCategoria =
            categoriaActiva.value === 'Todas'
            || producto.categoria === categoriaActiva.value

        if (!coincideCategoria) {
            return false
        }

        if (!termino) {
            return true
        }

        return String(producto.nombre ?? '').toLowerCase().includes(termino)
            || String(producto.codigo ?? '').toLowerCase().includes(termino)
            || String(producto.sabor ?? '').toLowerCase().includes(termino)

    })

})


const productosVisibles = computed(() => {

    if (mostrarTodos.value) {
        return productosFiltrados.value
    }

    return productosFiltrados.value.slice(0, 8)

})


watch([buscarStock, categoriaActiva], () => {
    mostrarTodos.value = false
})


const totalEnStock = computed(() => {

    return props.productosStock.reduce(
        (total, producto) => total + Number(producto.stock ?? 0),
        0
    )

})

const stockBajo = (producto) => {

    return Number(producto.stock ?? 0) <= Number(producto.minimo ?? 0)

}

const presentacion = (producto) => {

    const partes = []

    if (producto.empaque) {
        partes.push(producto.empaque)
    }

    if (producto.contenido) {
        partes.push(`${producto.contenido} ${producto.unidad ?? ''}`.trim())
    }

    return partes.join(' · ')

}


const productosIngreso = (ingreso) => {

    const detalles = ingreso?.detalles ?? []
    const vistos = new Set()
    const productos = []

    for (const detalle of detalles) {

        const variante = detalle.product_variant ?? detalle.productVariant
        const producto = variante?.product

        const clave = producto?.id ?? variante?.sku ?? detalle.id

        if (vistos.has(clave)) {
            continue
        }

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

    return (props.ingresos?.data ?? []).map((ingreso) => ({
        ingreso,
        productos: productosIngreso(ingreso),
    }))

})


const headers = [
    'Ingreso',
    'Productos',
    'Encargado',
    'Total',
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


        <!-- Catálogo de productos en stock -->

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div
                class="flex flex-col gap-4 border-b border-slate-100
                       bg-gradient-to-r from-emerald-50/60 via-white to-white
                       px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center
                               rounded-xl bg-emerald-100 text-emerald-700"
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-6 w-6"
                        >
                            <path d="M4 7l8-4 8 4v10l-8 4-8-4V7z" />
                            <path d="M4 7l8 4 8-4M12 11v10" />
                        </svg>

                    </div>

                    <div>

                        <h2 class="text-lg font-semibold text-slate-800">
                            Productos disponibles
                        </h2>

                        <p class="text-sm text-slate-500">
                            Existencias actuales por producto, sumando todos sus lotes.
                        </p>

                    </div>

                </div>


                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                    <span
                        class="inline-flex items-center gap-1.5 self-start rounded-full
                               bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700"
                    >
                        Total en stock
                        <strong class="text-sm">{{ totalEnStock }}</strong>
                    </span>

                    <input
                        v-model="buscarStock"
                        type="text"
                        placeholder="Buscar producto..."
                        class="w-full rounded-lg border-slate-300 text-sm
                               focus:border-emerald-500
                               focus:ring-emerald-500 sm:w-64"
                    />

                </div>

            </div>


            <div class="flex flex-wrap gap-2 px-5 pt-4">

                <button
                    v-for="categoria in categorias"
                    :key="categoria"
                    type="button"
                    @click="categoriaActiva = categoria"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition"
                    :class="categoriaActiva === categoria
                        ? 'bg-slate-800 text-white'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    {{ categoria }}
                </button>

            </div>


            <div class="p-5">

                <div
                    v-if="productosVisibles.length"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2
                           lg:grid-cols-3 xl:grid-cols-4"
                >

                    <article
                        v-for="producto in productosVisibles"
                        :key="producto.id"
                        class="group overflow-hidden rounded-2xl border
                               border-slate-200 bg-white shadow-sm transition
                               hover:-translate-y-0.5 hover:shadow-lg"
                    >

                        <div class="relative">

                            <ProductVisual
                                :name="producto.nombre"
                                :category="producto.categoria"
                                :image="producto.imagen"
                                class="aspect-[4/3] w-full"
                            />

                            <span
                                class="absolute left-2 top-2 max-w-[70%] truncate
                                       rounded-full bg-white/85 px-2 py-0.5
                                       text-[11px] font-medium text-slate-700
                                       backdrop-blur"
                            >
                                {{ producto.categoria ?? 'General' }}
                            </span>

                            <span
                                class="absolute right-2 top-2 rounded-full px-2 py-0.5
                                       text-[11px] font-semibold backdrop-blur"
                                :class="Number(producto.stock) > 0
                                    ? 'bg-white/90 text-emerald-700'
                                    : 'bg-rose-500 text-white'"
                            >
                                {{ producto.stock }} {{ producto.unidad }}
                            </span>

                        </div>


                        <div class="p-3">

                            <h3
                                class="truncate text-sm font-semibold text-slate-800"
                                :title="producto.nombre"
                            >
                                {{ producto.nombre }}
                            </h3>

                            <p class="mt-0.5 truncate text-xs text-slate-500">

                                <span v-if="producto.sabor">
                                    {{ producto.sabor }}
                                </span>

                                <span v-if="producto.sabor && presentacion(producto)">
                                    ·
                                </span>

                                <span v-if="presentacion(producto)">
                                    {{ presentacion(producto) }}
                                </span>

                                <span v-if="!producto.sabor && !presentacion(producto)">
                                    {{ producto.codigo }}
                                </span>

                            </p>


                            <div
                                class="mt-3 flex items-center justify-between gap-2
                                       border-t border-slate-100 pt-2"
                            >

                                <span
                                    v-if="stockBajo(producto)"
                                    class="rounded-full bg-amber-100 px-2 py-0.5
                                           text-[10px] font-semibold text-amber-700"
                                >
                                    Stock bajo
                                </span>

                                <span v-else class="text-[11px] text-slate-400">
                                    Mín. {{ producto.minimo }} {{ producto.unidad }}
                                </span>

                                <span class="font-mono text-[10px] text-slate-300">
                                    {{ producto.codigo }}
                                </span>

                            </div>

                        </div>

                    </article>

                </div>


                <div
                    v-else
                    class="rounded-xl border border-dashed border-slate-300
                           p-8 text-center text-sm text-slate-500"
                >
                    No hay productos que coincidan con la búsqueda.
                </div>


                <div
                    v-if="productosFiltrados.length > 8"
                    class="mt-5 flex justify-center"
                >

                    <button
                        type="button"
                        @click="mostrarTodos = !mostrarTodos"
                        class="rounded-full border border-slate-200 bg-white px-4 py-2
                               text-sm font-medium text-slate-600 shadow-sm
                               transition hover:bg-slate-50"
                    >
                        {{ mostrarTodos
                            ? 'Ver menos'
                            : `Ver todos (${productosFiltrados.length})`
                        }}
                    </button>

                </div>

            </div>

        </section>


        <!-- Historial de ingresos -->

        <div class="mb-3 flex items-center justify-between">

            <h2 class="text-lg font-semibold text-slate-800">
                Historial de ingresos
            </h2>

            <span class="text-sm text-slate-500">
                {{ ingresos.total }} registros
            </span>

        </div>


        <Table :headers="headers">

            <template v-for="fila in filas" :key="fila.ingreso.id">

                <tr class="border-b border-slate-200 transition hover:bg-slate-50">

                    <!-- Ingreso -->

                    <td data-label="Ingreso" class="p-4 align-top">

                        <div class="font-semibold text-slate-800">
                            {{ fila.ingreso.numero }}
                        </div>

                        <div class="mt-0.5 text-xs text-slate-400">
                            {{ formatoFecha(fila.ingreso.fecha) }}
                        </div>

                    </td>


                    <!-- Productos -->

                    <td data-label="Productos" class="p-4 align-top">

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
                                    <span
                                        v-if="producto.sabor"
                                        class="font-normal text-slate-400"
                                    >
                                        · {{ producto.sabor }}
                                    </span>
                                </div>

                                <div
                                    v-if="fila.productos.length > 2"
                                    class="text-xs text-slate-400"
                                >
                                    +{{ fila.productos.length - 2 }} más
                                </div>

                            </div>

                        </div>

                    </td>


                    <!-- Encargado -->

                    <td data-label="Encargado" class="p-4 align-top">

                        <div class="text-sm text-slate-700">
                            {{ fila.ingreso.encargado?.full_name || '-' }}
                        </div>

                        <div class="text-xs text-slate-400">
                            por {{ fila.ingreso.usuario?.name || '-' }}
                        </div>

                    </td>


                    <!-- Total -->

                    <td data-label="Total" class="p-4 align-top">

                        <span class="font-semibold text-slate-700">
                            Bs. {{ formatoMoneda(fila.ingreso.total) }}
                        </span>

                    </td>


                    <!-- Estado -->

                    <td data-label="Estado" class="p-4 align-top">

                        <Badge
                            :color="fila.ingreso.estado === 'activo' ? 'green' : 'red'"
                        >
                            {{ fila.ingreso.estado === 'activo' ? 'Activo' : 'Anulado' }}
                        </Badge>

                    </td>


                    <!-- Acciones -->

                    <td class="p-4 align-top table-actions">

                        <div class="flex flex-wrap gap-2">

                            <Link :href="route('ingresos.show', fila.ingreso.id)">
                                <Button color="secondary">
                                    Ver
                                </Button>
                            </Link>

                            <Link
                                v-if="fila.ingreso.estado === 'activo'"
                                :href="route('ingresos.edit', fila.ingreso.id)"
                            >
                                <Button color="info">
                                    Editar
                                </Button>
                            </Link>

                            <Button
                                v-if="fila.ingreso.estado === 'activo'"
                                color="danger"
                                @click="anular(fila.ingreso.id)"
                            >
                                Anular
                            </Button>

                        </div>

                    </td>

                </tr>

            </template>


            <tr v-if="filas.length === 0" class="table-empty">

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
