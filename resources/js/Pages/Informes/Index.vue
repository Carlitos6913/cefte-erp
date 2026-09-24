<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


const props = defineProps({
    resumen: Object,
    movimientos: Object,
    productosStockBajo: Number,
    productosTotal: Number,
    esAdministrador: Boolean,
    filtros: Object,
})

const form = reactive({
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
})

const filtrar = () => {
    router.get(route('informes.index'), form, {
        preserveState: true,
        replace: true,
    })
}

const limpiar = () => {
    form.fecha_inicio = ''
    form.fecha_fin = ''
    filtrar()
}


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}

const headersAdmin = [
    'Nro. Ingreso',
    'Fecha',
    'Proveedor',
    'Productos',
    'Total',
    'Estado',
    'Registrado por',
]

const headersGerente = [
    'Nro. Ingreso',
    'Fecha',
    'Proveedor',
    'Productos',
    'Total',
    'Estado',
]

</script>


<template>

    <Head title="Informes" />

    <AppLayout>

        <!-- Encabezado -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-800">
                Informes
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Resumen de movimientos y control del inventario
            </p>
        </div>


        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

            <!-- Total ingresos -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total ingresos</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">
                    {{ resumen.total_ingresos }}
                </p>
            </div>

            <!-- Monto total -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Monto total</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">
                    Bs. {{ formatoMoneda(resumen.monto_total) }}
                </p>
            </div>

            <!-- Anulados -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Ingresos anulados</p>
                <p class="mt-2 text-2xl font-bold text-red-600">
                    {{ resumen.total_anulados }}
                </p>
            </div>

            <!-- Stock bajo -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Productos con stock bajo
                </p>
                <p class="mt-2 text-2xl font-bold text-amber-600">
                    {{ productosStockBajo }}
                    <span class="text-sm font-normal text-slate-400">
                        / {{ productosTotal }}
                    </span>
                </p>
            </div>

        </div>


        <!-- Filtros de fecha -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
            <div class="flex flex-wrap items-end gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Fecha inicio
                    </label>
                    <input
                        v-model="form.fecha_inicio"
                        type="date"
                        class="border border-slate-300 rounded-lg px-3 py-2 text-sm
                               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                               outline-none transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Fecha fin
                    </label>
                    <input
                        v-model="form.fecha_fin"
                        type="date"
                        class="border border-slate-300 rounded-lg px-3 py-2 text-sm
                               focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                               outline-none transition"
                    />
                </div>

                <Button @click="filtrar">
                    Filtrar
                </Button>

                <Button color="secondary" @click="limpiar">
                    Limpiar
                </Button>

            </div>
        </div>


        <!-- Tabla de movimientos -->
        <Table :headers="esAdministrador ? headersAdmin : headersGerente">

            <tr
                v-for="mov in movimientos.data"
                :key="mov.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <!-- Número -->
                <td class="p-4">
                    <div class="font-medium text-slate-700">
                        {{ mov.numero }}
                    </div>
                </td>

                <!-- Fecha -->
                <td class="p-4 text-slate-600">
                    {{ formatoFecha(mov.fecha) }}
                </td>

                <!-- Proveedor -->
                <td class="p-4 text-slate-600">
                    {{ mov.proveedor || 'Sin proveedor' }}
                </td>

                <!-- Productos (detalles) -->
                <td class="p-4">
                    <div class="text-sm text-slate-600">
                        {{ mov.detalles_count }} producto(s)
                    </div>
                    <div
                        v-for="detalle in mov.detalles.slice(0, 2)"
                        :key="detalle.id"
                        class="text-xs text-slate-400"
                    >
                        {{ detalle.product_variant?.product?.name ?? '-' }}
                    </div>
                    <div
                        v-if="mov.detalles.length > 2"
                        class="text-xs text-slate-400"
                    >
                        +{{ mov.detalles.length - 2 }} más
                    </div>
                </td>

                <!-- Total -->
                <td class="p-4">
                    <span class="font-semibold text-slate-700">
                        Bs. {{ formatoMoneda(mov.total) }}
                    </span>
                </td>

                <!-- Estado -->
                <td class="p-4">
                    <Badge
                        :color="mov.estado === 'activo' ? 'green' : 'red'"
                    >
                        {{ mov.estado === 'activo' ? 'Activo' : 'Anulado' }}
                    </Badge>
                </td>

                <!-- Registrado por (solo administrador) -->
                <td
                    v-if="esAdministrador"
                    class="p-4 text-slate-600"
                >
                    {{ mov.usuario?.name || '-' }}
                </td>

            </tr>

            <!-- Sin resultados -->
            <tr v-if="movimientos.data.length === 0">
                <td
                    :colspan="esAdministrador ? 7 : 6"
                    class="p-8 text-center text-slate-500"
                >
                    No existen movimientos para el periodo seleccionado.
                </td>
            </tr>

        </Table>


        <!-- Paginación -->
        <div
            v-if="movimientos.links && movimientos.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template
                v-for="link in movimientos.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
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
