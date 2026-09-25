<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    productos: Array,
    clientes: Array,
})


const form = useForm({
    fecha: new Date().toISOString().split('T')[0],
    cliente_id: '',
    cliente: '',
    observaciones: '',
    pago_tipo: 'contado',
    detalles: [
        {
            product_variant_id: '',
            cantidad: 1,
            precio_unitario: 0
        }
    ]
})


const agregarProducto = () => {
    form.detalles.push({
        product_variant_id: '',
        cantidad: 1,
        precio_unitario: 0
    })
}


const eliminarProducto = (index) => {
    if (form.detalles.length === 1) return
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


const stockDisponible = (detalle) => {
    const producto = productoSeleccionado(detalle)
    return producto ? Number(producto.saldo_autorizado ?? 0) : 0
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


const seleccionarCliente = () => {
    const cliente = props.clientes.find(c => c.id == form.cliente_id)
    if (cliente) {
        form.cliente = cliente.nombre
    } else {
        form.cliente = ''
    }
}


const registrar = () => {
    form.post(
        route('ventas.store'),
        { preserveScroll: true }
    )
}

</script>


<template>

    <Head title="Nueva venta" />

    <AppLayout>

        <div class="max-w-6xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('ventas.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Nueva venta
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Registra la venta de productos a un cliente.
                </p>

            </div>


            <form
                @submit.prevent="registrar"
                class="space-y-6"
            >

                <!-- Información general -->

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">

                    <h2 class="text-lg font-semibold text-slate-800 mb-5">
                        Información de la venta
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
                                placeholder="Nombre del cliente"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Tipo de pago *
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
                            <p v-if="form.pago_tipo === 'credito'" class="text-xs text-amber-600 mt-1">
                                El saldo total quedará pendiente de cobro.
                            </p>
                        </div>


                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Observaciones
                            </label>
                            <textarea
                                v-model="form.observaciones"
                                rows="3"
                                placeholder="Observaciones de la venta..."
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            ></textarea>
                        </div>

                    </div>

                </div>


                <!-- Productos -->

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">

                    <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">

                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">
                                Productos
                            </h2>
                            <p class="text-sm text-slate-500">
                                Agrega los productos a vender.
                            </p>
                        </div>

                        <Button type="button" color="secondary" @click="agregarProducto">
                            + Agregar producto
                        </Button>

                    </div>


                    <div class="overflow-x-auto">

                        <div class="border-b border-slate-200">
                            <p class="text-sm text-slate-500 px-6 pb-4">
                                Solo se pueden vender productos con salida de despacho (motivo «Despacho para venta») aún autorizada.
                            </p>
                        </div>

                        <table class="w-full">


                            <thead class="bg-slate-50">

                                <tr>
                                    <th class="p-4 text-left text-sm font-semibold">
                                        Producto
                                    </th>
                                    <th class="p-4 text-left text-sm font-semibold">
                                        Autorizado
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

                                    <td class="p-4 min-w-[280px]">
                                        <select
                                            v-model="detalle.product_variant_id"
                                            @change="actualizarPrecio(detalle)"
                                            class="w-full rounded-lg border-slate-300
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                        >
                                            <option value="">Seleccionar producto</option>
                                            <option
                                                v-for="producto in productos"
                                                :key="producto.id"
                                                :value="producto.id"
                                            >
                                                {{ producto.sku }} - {{ producto.product?.name }}
                                            </option>
                                        </select>
                                    </td>

                                    <td class="p-4 w-24">
                                        <span
                                            class="text-sm font-medium"
                                            :class="stockDisponible(detalle) > 0
                                                ? 'text-slate-600'
                                                : 'text-red-600'"
                                        >
                                            {{ stockDisponible(detalle) }}
                                        </span>
                                    </td>

                                    <td class="p-4 w-36">
                                        <input
                                            v-model.number="detalle.cantidad"
                                            type="number"
                                            min="1"
                                            :max="stockDisponible(detalle)"
                                            class="w-full rounded-lg border-slate-300
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                        />
                                    </td>

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

                                    <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">
                                        Bs. {{ subtotal(detalle).toFixed(2) }}
                                    </td>

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


                    <div class="flex justify-end px-6 py-5 border-t border-slate-200 bg-slate-50">

                        <div class="text-right">
                            <p class="text-sm text-slate-500">
                                Total de la venta
                            </p>
                            <p class="text-2xl font-bold text-slate-800">
                                Bs. {{ total().toFixed(2) }}
                            </p>
                            <p
                                v-if="form.pago_tipo === 'credito'"
                                class="text-sm text-amber-600 mt-1"
                            >
                                Saldo a cobrar: Bs. {{ total().toFixed(2) }}
                            </p>
                        </div>

                    </div>

                </div>


                <div
                    v-if="form.errors.detalles"
                    class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"
                >
                    {{ form.errors.detalles }}
                </div>


                <div class="flex justify-end gap-3">

                    <Link :href="route('ventas.index')">
                        <Button type="button" color="secondary">
                            Cancelar
                        </Button>
                    </Link>

                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Registrando...' : 'Registrar venta' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
