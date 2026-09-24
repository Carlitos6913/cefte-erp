<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    categorias: Array,
    productos: Array
})

const form = useForm({

    fecha: new Date().toISOString().split('T')[0],

    categoria_id: '',

    proveedor: '',

    observaciones: '',

    detalles: [
        {
            product_variant_id: '',
            cantidad: 1,
            precio_unitario: 0
        }
    ]

})


const productosPorCategoria = computed(() => {

    if (!form.categoria_id) {
        return []
    }

    return props.productos.filter(
        producto => producto.product?.category_id == form.categoria_id
    )

})


const cambiarCategoria = () => {

    form.detalles.forEach(detalle => {
        detalle.product_variant_id = ''
        detalle.precio_unitario = 0
    })

}


const agregarProducto = () => {

    form.detalles.push({
        product_variant_id: '',
        cantidad: 1,
        precio_unitario: 0
    })

}


const eliminarProducto = (index) => {

    if (form.detalles.length === 1) {
        return
    }

    form.detalles.splice(index, 1)

}


const productoSeleccionado = (detalle) => {

    return props.productos.find(
        producto => producto.id == detalle.product_variant_id
    )

}


const actualizarPrecio = (detalle) => {

    const producto = productoSeleccionado(detalle)

    if (producto) {
        detalle.precio_unitario = Number(producto.unit_price ?? 0)
    }

}


const subtotal = (detalle) => {

    return (
        Number(detalle.cantidad || 0) *
        Number(detalle.precio_unitario || 0)
    )

}


const total = () => {

    return form.detalles.reduce(
        (total, detalle) => total + subtotal(detalle),
        0
    )

}


const registrar = () => {

    form.post(
        route('ingresos.store'),
        {
            preserveScroll: true
        }
    )

}

</script>


<template>

    <Head title="Nuevo ingreso" />

    <AppLayout>

        <div class="max-w-6xl mx-auto">

            <!-- Encabezado -->

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('ingresos.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Nuevo ingreso
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Registra la entrada de productos al almacén.
                </p>

            </div>


            <form
                @submit.prevent="registrar"
                class="space-y-6"
            >

                <!-- Información general -->

                <div
                    class="bg-white rounded-xl border border-slate-200
                           shadow-sm p-6"
                >

                    <h2 class="text-lg font-semibold text-slate-800 mb-5">
                        Información del ingreso
                    </h2>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Fecha -->

                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-slate-700 mb-1"
                            >
                                Fecha
                            </label>

                            <input
                                v-model="form.fecha"
                                type="date"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.fecha"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.fecha }}
                            </p>

                        </div>


                        <!-- Proveedor -->

                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-slate-700 mb-1"
                            >
                                Proveedor
                            </label>

                            <input
                                v-model="form.proveedor"
                                type="text"
                                placeholder="Nombre del proveedor"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.proveedor"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.proveedor }}
                            </p>

                        </div>


                        <!-- Observaciones -->

                        <div class="md:col-span-2">

                            <label
                                class="block text-sm font-medium
                                       text-slate-700 mb-1"
                            >
                                Observaciones
                            </label>

                            <textarea
                                v-model="form.observaciones"
                                rows="3"
                                placeholder="Observaciones del ingreso..."
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            ></textarea>

                        </div>

                    </div>

                </div>


                <!-- Productos -->

                <div
                    class="bg-white rounded-xl border border-slate-200
                           shadow-sm"
                >

                    <div
                        class="flex justify-between items-center
                               px-6 py-4 border-b border-slate-200"
                    >

                        <div>

                            <h2 class="text-lg font-semibold text-slate-800">
                                Productos
                            </h2>

                            <p class="text-sm text-slate-500">
                                Agrega los productos recibidos.
                            </p>

                        </div>


                        <Button
                            type="button"
                            color="secondary"
                            @click="agregarProducto"
                        >
                            + Agregar producto
                        </Button>

                    </div>


                    <!-- Selector de categoría -->

                    <div class="px-6 py-4 border-b border-slate-200">

                        <label
                            class="block text-sm font-medium
                                   text-slate-700 mb-1"
                        >
                            Categoría
                        </label>

                        <select
                            v-model="form.categoria_id"
                            @change="cambiarCategoria"
                            class="w-full md:w-96 rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        >

                            <option value="">
                                Seleccionar categoría
                            </option>

                            <option
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                :value="categoria.id"
                            >
                                {{ categoria.name }}
                            </option>

                        </select>

                        <p class="text-xs text-slate-500 mt-1">
                            Selecciona una categoría para cargar sus productos.
                        </p>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead class="bg-slate-50">

                                <tr>

                                    <th class="p-4 text-left text-sm font-semibold">
                                        Producto
                                    </th>

                                    <th class="p-4 text-left text-sm font-semibold">
                                        Cantidad
                                    </th>

                                    <th class="p-4 text-left text-sm font-semibold">
                                        Precio unitario
                                    </th>

                                    <th class="p-4 text-left text-sm font-semibold">
                                        Subtotal
                                    </th>

                                    <th class="p-4"></th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr
                                    v-for="(detalle, index) in form.detalles"
                                    :key="index"
                                    class="border-t border-slate-200"
                                >

                                    <!-- Producto -->

                                    <td class="p-4 min-w-[280px]">

                                        <select
                                            v-model="detalle.product_variant_id"
                                            @change="actualizarPrecio(detalle)"
                                            class="w-full rounded-lg border-slate-300
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                            :disabled="!form.categoria_id"
                                        >

                                            <option value="">
                                                {{
                                                    form.categoria_id
                                                        ? 'Seleccionar producto'
                                                        : 'Primero selecciona una categoría'
                                                }}
                                            </option>

                                            <option
                                                v-for="producto in productosPorCategoria"
                                                :key="producto.id"
                                                :value="producto.id"
                                            >
                                                {{ producto.sku }}
                                                -
                                                {{ producto.product?.name }}
                                            </option>

                                        </select>

                                    </td>


                                    <!-- Cantidad -->

                                    <td class="p-4 w-36">

                                        <input
                                            v-model.number="detalle.cantidad"
                                            type="number"
                                            min="1"
                                            class="w-full rounded-lg border-slate-300
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                        />

                                    </td>


                                    <!-- Precio -->

                                    <td class="p-4 w-44">

                                        <input
                                            v-model.number="detalle.precio_unitario"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-lg border-slate-300
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                        />

                                    </td>


                                    <!-- Subtotal -->

                                    <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">

                                        Bs.
                                        {{ subtotal(detalle).toFixed(2) }}

                                    </td>


                                    <!-- Eliminar -->

                                    <td class="p-4">

                                        <button
                                            type="button"
                                            @click="eliminarProducto(index)"
                                            :disabled="form.detalles.length === 1"
                                            class="text-red-500 hover:text-red-700
                                                   disabled:text-slate-300
                                                   disabled:cursor-not-allowed"
                                        >
                                            ✕
                                        </button>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <!-- Total -->

                    <div
                        class="flex justify-end px-6 py-5
                               border-t border-slate-200 bg-slate-50"
                    >

                        <div class="text-right">

                            <p class="text-sm text-slate-500">
                                Total del ingreso
                            </p>

                            <p class="text-2xl font-bold text-slate-800">
                                Bs. {{ total().toFixed(2) }}
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Errores -->

                <div
                    v-if="form.errors.detalles"
                    class="bg-red-50 border border-red-200
                           text-red-700 rounded-lg p-4"
                >
                    {{ form.errors.detalles }}
                </div>


                <!-- Acciones -->

                <div class="flex justify-end gap-3">

                    <Link :href="route('ingresos.index')">

                        <Button
                            type="button"
                            color="secondary"
                        >
                            Cancelar
                        </Button>

                    </Link>


                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >

                        {{
                            form.processing
                                ? 'Registrando...'
                                : 'Registrar ingreso'
                        }}

                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
