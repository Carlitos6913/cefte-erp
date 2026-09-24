<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    ingreso: Object,
    categorias: Array,
    productos: Array,
})

const form = useForm({
    fecha: props.ingreso.fecha,
    proveedor: props.ingreso.proveedor ?? '',
    observaciones: props.ingreso.observaciones ?? '',

    categoria_id: '',
})

const varianteInicial = props.productos.find(
    p => p.id === props.ingreso.detalles[0]?.product_variant_id
)

form.categoria_id = varianteInicial?.product?.category_id ?? ''

form.detalles = props.ingreso.detalles.map(detalle => ({
    product_variant_id: detalle.product_variant_id,
    cantidad: detalle.cantidad,
    precio_unitario: detalle.precio_unitario,
}))

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

const agregarDetalle = () => {
    form.detalles.push({
        product_variant_id: '',
        cantidad: 1,
        precio_unitario: 0,
    })
}

const eliminarDetalle = (index) => {
    if (form.detalles.length <= 1) {
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
        detalle.precio_unitario = producto.unit_price ?? 0
    }
}

const subtotal = (detalle) => {
    return Number(detalle.cantidad || 0)
        * Number(detalle.precio_unitario || 0)
}

const total = () => {
    return form.detalles.reduce(
        (sum, detalle) => sum + subtotal(detalle),
        0
    )
}

const actualizar = () => {

    form.put(route('ingresos.update', props.ingreso.id), {
        preserveScroll: true,
    })

}

</script>


<template>

<Head title="Editar ingreso" />

<AppLayout>

    <!-- Encabezado -->

    <div class="mb-6">

        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-3xl font-bold text-slate-800">
                    Editar ingreso
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    {{ ingreso.numero }}
                </p>

            </div>

            <Link :href="route('ingresos.show', ingreso.id)">

                <Button color="secondary">
                    Cancelar
                </Button>

            </Link>

        </div>

    </div>


    <!-- Información general -->

    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">

        <h2 class="text-lg font-semibold text-slate-800 mb-5">
            Información del ingreso
        </h2>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <!-- Fecha -->

            <div>

                <label class="block text-sm font-medium text-slate-700 mb-1">
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

                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Proveedor
                </label>

                <input
                    v-model="form.proveedor"
                    type="text"
                    class="w-full rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                    placeholder="Nombre del proveedor"
                />

                <p
                    v-if="form.errors.proveedor"
                    class="text-sm text-red-600 mt-1"
                >
                    {{ form.errors.proveedor }}
                </p>

            </div>


            <!-- Número -->

            <div>

                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Número
                </label>

                <input
                    :value="ingreso.numero"
                    type="text"
                    disabled
                    class="w-full rounded-lg border-slate-200
                           bg-slate-100 text-slate-500"
                />

            </div>

        </div>


        <!-- Observaciones -->

        <div class="mt-5">

            <label class="block text-sm font-medium text-slate-700 mb-1">
                Observaciones
            </label>

            <textarea
                v-model="form.observaciones"
                rows="3"
                class="w-full rounded-lg border-slate-300
                       focus:border-emerald-500
                       focus:ring-emerald-500"
                placeholder="Observaciones del ingreso..."
            ></textarea>

        </div>

    </div>


    <!-- Productos -->

    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <div class="flex items-center justify-between mb-5">

            <div>

                <h2 class="text-lg font-semibold text-slate-800">
                    Productos
                </h2>

                <p class="text-sm text-slate-500">
                    Modifica las cantidades, productos o precios.
                </p>

            </div>


            <Button
                color="info"
                @click="agregarDetalle"
            >
                + Agregar producto
            </Button>

        </div>


        <!-- Selector de categoría -->

        <div class="mb-5">

            <label class="block text-sm font-medium text-slate-700 mb-1">
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

                <thead>

                    <tr class="border-b border-slate-200">

                        <th class="text-left p-3 text-sm font-semibold text-slate-600">
                            Producto
                        </th>

                        <th class="text-left p-3 text-sm font-semibold text-slate-600">
                            Cantidad
                        </th>

                        <th class="text-left p-3 text-sm font-semibold text-slate-600">
                            Precio unitario
                        </th>

                        <th class="text-right p-3 text-sm font-semibold text-slate-600">
                            Subtotal
                        </th>

                        <th class="p-3">
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <tr
                        v-for="(detalle, index) in form.detalles"
                        :key="index"
                        class="border-b border-slate-100"
                    >

                        <!-- Producto -->

                        <td class="p-3">

                            <select
                                v-model="detalle.product_variant_id"
                                @change="actualizarPrecio(detalle)"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                                :disabled="!form.categoria_id"
                            >

                                <option value="" disabled>
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
                                    {{ producto.sku }} -
                                    {{ producto.product?.name }}
                                </option>

                            </select>

                        </td>


                        <!-- Cantidad -->

                        <td class="p-3">

                            <input
                                v-model.number="detalle.cantidad"
                                type="number"
                                min="1"
                                class="w-28 rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                        </td>


                        <!-- Precio -->

                        <td class="p-3">

                            <input
                                v-model.number="detalle.precio_unitario"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-36 rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                        </td>


                        <!-- Subtotal -->

                        <td class="p-3 text-right font-medium text-slate-700">

                            Bs.
                            {{ subtotal(detalle).toFixed(2) }}

                        </td>


                        <!-- Eliminar -->

                        <td class="p-3 text-right">

                            <button
                                type="button"
                                @click="eliminarDetalle(index)"
                                :disabled="form.detalles.length <= 1"
                                class="text-red-500 hover:text-red-700
                                       disabled:text-slate-300"
                            >
                                Eliminar
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- Total -->

        <div class="flex justify-end mt-6">

            <div class="text-right">

                <p class="text-sm text-slate-500">
                    Total
                </p>

                <p class="text-2xl font-bold text-slate-800">
                    Bs. {{ total().toFixed(2) }}
                </p>

            </div>

        </div>


        <!-- Errores -->

        <div
            v-if="form.errors.detalles"
            class="mt-4 p-3 rounded-lg bg-red-50
                   border border-red-200 text-red-600 text-sm"
        >
            {{ form.errors.detalles }}
        </div>


        <!-- Acciones -->

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200">

            <Link :href="route('ingresos.show', ingreso.id)">

                <Button color="secondary">
                    Cancelar
                </Button>

            </Link>


            <Button
                :disabled="form.processing"
                @click="actualizar"
            >
                {{ form.processing
                    ? 'Guardando...'
                    : 'Guardar cambios'
                }}
            </Button>

        </div>

    </div>

</AppLayout>

</template>
