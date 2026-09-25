<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    venta: Object,
    productos: Array,
    clientes: Array,
})

const form = useForm({
    fecha: props.venta.fecha,
    cliente_id: props.venta.cliente_id ?? '',
    cliente: props.venta.cliente ?? '',
    observaciones: props.venta.observaciones ?? '',
    pago_tipo: props.venta.pago_tipo ?? 'contado',
    detalles: props.venta.detalles.map(detalle => ({
        product_variant_id: detalle.product_variant_id,
        cantidad: detalle.cantidad,
        precio_unitario: detalle.precio_unitario,
    })),
})

const agregarDetalle = () => {
    form.detalles.push({
        product_variant_id: '',
        cantidad: 1,
        precio_unitario: 0,
    })
}

const eliminarDetalle = (index) => {
    if (form.detalles.length <= 1) return
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

const stockDisponible = (detalle) => {
    const producto = productoSeleccionado(detalle)
    if (!producto) return 0

    const consumido = props.venta.detalles
        .filter(d => d.product_variant_id == detalle.product_variant_id)
        .reduce((sum, d) => sum + d.cantidad, 0)

    return Number(producto.saldo_autorizado ?? 0) + consumido
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

const seleccionarCliente = () => {
    const cliente = props.clientes.find(c => c.id == form.cliente_id)
    if (cliente) {
        form.cliente = cliente.nombre
    } else {
        form.cliente = ''
    }
}

const actualizar = () => {
    form.put(route('ventas.update', props.venta.id), {
        preserveScroll: true,
    })
}

</script>


<template>

<Head title="Editar venta" />

<AppLayout>

    <div class="mb-6">

        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Editar venta
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ venta.numero }}
                </p>
            </div>

            <Link :href="route('ventas.show', venta.id)">
                <Button color="secondary">
                    Cancelar
                </Button>
            </Link>

        </div>

    </div>


    <!-- Información general -->

    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">

        <h2 class="text-lg font-semibold text-slate-800 mb-5">
            Información de la venta
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

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
                <p v-if="form.errors.fecha" class="text-sm text-red-600 mt-1">
                    {{ form.errors.fecha }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Cliente (registrado)
                </label>
                <select
                    v-model="form.cliente_id"
                    @change="seleccionarCliente"
                    class="w-full rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                >
                    <option value="">Sin cliente registrado</option>
                    <option
                        v-for="cliente in clientes"
                        :key="cliente.id"
                        :value="cliente.id"
                    >
                        {{ cliente.nombre }}
                        {{ cliente.ci_nit ? `(${cliente.ci_nit})` : '' }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre cliente (referencia)
                </label>
                <input
                    v-model="form.cliente"
                    type="text"
                    class="w-full rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                    placeholder="Nombre del cliente"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Tipo de pago
                </label>
                <select
                    v-model="form.pago_tipo"
                    class="w-full rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                >
                    <option value="contado">Contado</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Número
                </label>
                <input
                    :value="venta.numero"
                    type="text"
                    disabled
                    class="w-full rounded-lg border-slate-200
                           bg-slate-100 text-slate-500"
                />
            </div>

        </div>

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
                placeholder="Observaciones de la venta..."
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
                        Solo hay saldo autorizado para venta si existe una salida de despacho activa.
                    </p>
            </div>

            <Button color="info" @click="agregarDetalle">
                + Agregar producto
            </Button>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>

                    <tr class="border-b border-slate-200">
                        <th class="text-left p-3 text-sm font-semibold text-slate-600">
                            Producto
                        </th>
                        <th class="text-left p-3 text-sm font-semibold text-slate-600">
                            Autorizado
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
                        <th class="p-3"></th>
                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="(detalle, index) in form.detalles"
                        :key="index"
                        class="border-b border-slate-100"
                    >

                        <td class="p-3">
                            <select
                                v-model="detalle.product_variant_id"
                                @change="actualizarPrecio(detalle)"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            >
                                <option value="" disabled>
                                    Seleccionar producto
                                </option>
                                <option
                                    v-for="producto in productos"
                                    :key="producto.id"
                                    :value="producto.id"
                                >
                                    {{ producto.sku }} - {{ producto.product?.name }}
                                </option>
                            </select>
                        </td>

                        <td class="p-3 w-24">
                            <span
                                class="text-sm font-medium"
                                :class="stockDisponible(detalle) > 0
                                    ? 'text-slate-600'
                                    : 'text-red-600'"
                            >
                                {{ stockDisponible(detalle) }}
                            </span>
                        </td>

                        <td class="p-3">
                            <input
                                v-model.number="detalle.cantidad"
                                type="number"
                                min="1"
                                :max="stockDisponible(detalle)"
                                class="w-28 rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />
                        </td>

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

                        <td class="p-3 text-right font-medium text-slate-700">
                            Bs. {{ subtotal(detalle).toFixed(2) }}
                        </td>

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


        <div class="flex justify-end mt-6">
            <div class="text-right">
                <p class="text-sm text-slate-500">Total</p>
                <p class="text-2xl font-bold text-slate-800">
                    Bs. {{ total().toFixed(2) }}
                </p>
            </div>
        </div>


        <div
            v-if="form.errors.detalles"
            class="mt-4 p-3 rounded-lg bg-red-50
                   border border-red-200 text-red-600 text-sm"
        >
            {{ form.errors.detalles }}
        </div>


        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200">

            <Link :href="route('ventas.show', venta.id)">
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
