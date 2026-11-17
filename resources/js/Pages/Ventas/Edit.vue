<script setup>

import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import ProductPickerModal from '@/Components/ProductPickerModal.vue'
import ProductVisual from '@/Components/ProductVisual.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    formatearCantidad,
    presentacionVariante,
    simboloUnidad,
} from '@/utils/producto'

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
    detalles: [],
})

const productoSeleccionado = (detalle) => {
    return props.productos.find(
        producto => producto.id == detalle.product_variant_id
    )
}

/**
 * Unidades en las que se puede capturar la cantidad: la unidad de
 * venta y, si tiene empaque configurado, la caja.
 */
const unidadesDe = (detalle) => {
    return productoSeleccionado(detalle)?.unidades ?? []
}

const unidadDe = (detalle) => simboloUnidad(productoSeleccionado(detalle))

const unidadSeleccionada = (detalle) => (
    unidadesDe(detalle).find(
        unidad => String(unidad.id) === String(detalle.unidad_id)
    )?.symbol ?? unidadDe(detalle)
)

/**
 * Cuántas unidades base representa una unidad del selector.
 */
const factorDe = (detalle) => {
    const unidad = unidadesDe(detalle).find(
        item => item.id == detalle.unidad_id
    )

    return Number(unidad?.factor ?? 1)
}

/**
 * El catálogo cotiza por unidad. Si el usuario elige la caja, el
 * precio de referencia se multiplica por el factor y el subtotal no
 * cambia.
 */
const actualizarPrecio = (detalle) => {
    const producto = productoSeleccionado(detalle)

    if (!producto) return

    if (!detalle.unidad_id && producto.unidades?.length) {
        detalle.unidad_id = producto.unidades[0].id
    }

    detalle.precio_unitario = Number(
        (Number(producto.unit_price ?? 0) * factorDe(detalle)).toFixed(2)
    )
}

const cambiarUnidad = (detalle) => {
    actualizarPrecio(detalle)
}

/**
 * Saldo autorizado en unidad base más lo ya consumido por esta venta,
 * expresado en la unidad seleccionada.
 */
const stockDisponible = (detalle) => {
    const producto = productoSeleccionado(detalle)

    if (!producto) return 0

    const consumido = props.venta.detalles
        .filter(d => d.product_variant_id == detalle.product_variant_id)
        .reduce((sum, d) => sum + d.cantidad, 0)

    return (
        Number(producto.saldo_autorizado ?? 0) + consumido
    ) / factorDe(detalle)
}

/**
 * Equivalencia en unidad base de lo capturado.
 */
const equivalencia = (detalle) => {

    const producto = productoSeleccionado(detalle)
    const factor = factorDe(detalle)

    if (!producto?.unidad_base || factor === 1) {
        return null
    }

    return `${Number(detalle.cantidad || 0) * factor} ${producto.unidad_base.symbol}`

}

const selectorAbierto = ref(false)
const indiceEditando = ref(null)

const variantePreseleccionada = computed(() => {
    if (indiceEditando.value === null) return null

    return form.detalles[indiceEditando.value]?.product_variant_id ?? null
})

const abrirSelector = (index = null) => {
    indiceEditando.value = index
    selectorAbierto.value = true
}

const cerrarSelector = () => {
    selectorAbierto.value = false
    indiceEditando.value = null
}

const agregarDetalle = () => {
    abrirSelector()
}

const confirmarVariante = (variante) => {
    let detalle

    if (indiceEditando.value === null) {
        detalle = {
            product_variant_id: variante.id,
            unidad_id: '',
            cantidad: 1,
            precio_unitario: 0,
        }

        form.detalles.push(detalle)
    } else {
        detalle = form.detalles[indiceEditando.value]

        if (!detalle) {
            cerrarSelector()
            return
        }

        const cambio = String(detalle.product_variant_id) !== String(variante.id)

        detalle.product_variant_id = variante.id

        if (cambio) {
            detalle.unidad_id = ''
            detalle.cantidad = 1
        }
    }

    actualizarPrecio(detalle)
    cerrarSelector()
}

const eliminarDetalle = (index) => {
    form.detalles.splice(index, 1)
}

const subtotal = (detalle) => {
    return Number(detalle.cantidad || 0)
        * Number(detalle.precio_unitario || 0)
}

/*
 * Los detalles existentes guardan `cantidad` en unidad base, pero el
 * formulario los presenta como los capturó el usuario: "2 cajas" con
 * su precio por caja.
 */
form.detalles = props.venta.detalles.map(detalle => {

    const unidadId = detalle.unidad_id
        ?? props.productos.find(
            p => p.id === detalle.product_variant_id
        )?.unidad_base_id
        ?? ''

    const factor = Number(
        props.productos
            .find(p => p.id === detalle.product_variant_id)
            ?.unidades?.find(u => u.id == unidadId)?.factor ?? 1
    )

    return {
        product_variant_id: detalle.product_variant_id,
        unidad_id: unidadId,
        cantidad: Number(detalle.cantidad_original ?? detalle.cantidad),
        precio_unitario: Number(
            (Number(detalle.precio_unitario ?? 0) * factor).toFixed(2)
        ),
    }

})

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
    if (!form.detalles.length) return

    form.put(route('ventas.update', props.venta.id), {
        preserveScroll: true,
    })
}

</script>


<template>

<Head title="Editar venta" />

<AppLayout>

    <div class="max-w-6xl mx-auto">

        <PageHeader title="Editar venta" :subtitle="venta.numero">
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.765 0 1.44-.486 1.68-1.214l1.605-4.815A1.125 1.125 0 0021 6.75H5.106M7.5 14.25L5.106 6.75m0 0L4.5 4.5" />
                </svg>
            </template>
            <template #actions>
                <Link :href="route('ventas.show', venta.id)">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al detalle
                    </Button>
                </Link>
            </template>
        </PageHeader>


        <form @submit.prevent="actualizar">

            <FormLayout :aside="true">

                <FormSection
                    title="Datos de la venta"
                    description="Actualiza la fecha, la condición de pago y las observaciones."
                    tone="amber"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <FormField label="Fecha" for-id="fecha" :error="form.errors.fecha" required>
                            <input
                                id="fecha"
                                v-model="form.fecha"
                                type="date"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.fecha }"
                            />
                        </FormField>

                        <FormField label="Tipo de pago" for-id="pago_tipo" :error="form.errors.pago_tipo" required>
                            <select
                                id="pago_tipo"
                                v-model="form.pago_tipo"
                                class="form-select"
                                :class="{ 'is-invalid': form.errors.pago_tipo }"
                            >
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </FormField>

                        <div class="md:col-span-2">
                            <FormField label="Observaciones" for-id="observaciones" :error="form.errors.observaciones" optional>
                                <textarea
                                    id="observaciones"
                                    v-model="form.observaciones"
                                    rows="3"
                                    placeholder="Observaciones de la venta..."
                                    class="form-textarea"
                                    :class="{ 'is-invalid': form.errors.observaciones }"
                                ></textarea>
                            </FormField>
                        </div>
                    </div>
                </FormSection>

                <FormSection
                    title="Cliente"
                    description="Asocia la venta a un cliente registrado o deja una referencia."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <FormField label="Cliente registrado" for-id="cliente_id" :error="form.errors.cliente_id" optional>
                            <select
                                id="cliente_id"
                                v-model="form.cliente_id"
                                @change="seleccionarCliente"
                                class="form-select"
                                :class="{ 'is-invalid': form.errors.cliente_id }"
                            >
                                <option value="">Sin cliente registrado</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }}
                                    {{ cliente.ci_nit ? `(${cliente.ci_nit})` : '' }}
                                </option>
                            </select>
                        </FormField>

                        <FormField label="Nombre cliente" for-id="cliente" :error="form.errors.cliente" optional hint="Nombre de referencia para la venta.">
                            <input
                                id="cliente"
                                v-model="form.cliente"
                                type="text"
                                class="form-input"
                                placeholder="Nombre del cliente"
                                :class="{ 'is-invalid': form.errors.cliente }"
                            />
                        </FormField>
                    </div>
                </FormSection>

                <FormSection
                    title="Productos"
                    description="Modifica las cantidades, productos o precios. Solo hay saldo autorizado para venta si existe una salida de despacho activa."
                    tone="blue"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0l-8.25 4.5m8.25-4.5v9l-8.25 4.5m0-9L3.75 7.5m8.25 4.5v9m-8.25-13.5v9l8.25 4.5" />
                        </svg>
                    </template>
                    <template #actions>
                        <Button type="button" color="secondary" @click="agregarDetalle">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Agregar producto
                        </Button>
                    </template>

                    <div
                        v-if="!form.detalles.length"
                        class="rounded-xl border border-dashed border-slate-200 bg-slate-50/40"
                    >
                        <EmptyState
                            title="Esta venta no tiene productos"
                            message="Agrega al menos un producto para poder guardar los cambios."
                        >
                            <template #action>
                                <Button type="button" @click="agregarDetalle">
                                    Agregar producto
                                </Button>
                            </template>
                        </EmptyState>
                    </div>

                    <div v-else class="-mx-5 sm:-mx-6 -mt-5 sm:-mt-6 overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Producto</th>
                                    <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Unidad</th>
                                    <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Autorizado</th>
                                    <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Cantidad</th>
                                    <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Precio unitario</th>
                                    <th class="px-5 sm:px-6 py-3 text-right text-[11px] uppercase tracking-wider font-semibold text-slate-500">Subtotal</th>
                                    <th class="px-5 sm:px-6 py-3 text-right text-[11px] uppercase tracking-wider font-semibold text-slate-500">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(detalle, index) in form.detalles" :key="index" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 sm:px-6 py-4 min-w-[300px]">
                                        <div v-if="productoSeleccionado(detalle)" class="flex items-start gap-3">
                                            <ProductVisual
                                                :name="productoSeleccionado(detalle).product?.name"
                                                :category="productoSeleccionado(detalle).product?.category?.name"
                                                :image="productoSeleccionado(detalle).product?.image_url"
                                                class="h-11 w-11 shrink-0 rounded-xl"
                                            />

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-800">
                                                    {{ productoSeleccionado(detalle).product?.name }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ presentacionVariante(productoSeleccionado(detalle)) || 'Presentación única' }}
                                                </p>
                                                <p class="mt-0.5 font-mono text-[11px] text-slate-400">
                                                    {{ productoSeleccionado(detalle).sku }}
                                                </p>

                                                <button
                                                    type="button"
                                                    class="mt-1 text-xs font-semibold text-emerald-600 transition hover:text-emerald-700"
                                                    @click="abrirSelector(index)"
                                                >
                                                    Cambiar presentación
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 sm:px-6 py-4 w-40">
                                        <select
                                            v-model="detalle.unidad_id"
                                            @change="cambiarUnidad(detalle)"
                                            :disabled="!detalle.product_variant_id || unidadesDe(detalle).length < 2"
                                            class="form-select"
                                        >
                                            <option v-for="unidad in unidadesDe(detalle)" :key="unidad.id" :value="unidad.id">
                                                {{ unidad.name }}<span v-if="unidad.factor > 1"> ({{ unidad.factor }} {{ unidadesDe(detalle)[0]?.symbol }})</span>
                                            </option>
                                        </select>
                                    </td>

                                    <td class="px-5 sm:px-6 py-4 w-28">
                                        <span
                                            class="text-sm font-medium"
                                            :class="stockDisponible(detalle) > 0 ? 'text-slate-600' : 'text-red-600'"
                                        >
                                            {{ formatearCantidad(stockDisponible(detalle)) }} {{ unidadSeleccionada(detalle) }}
                                        </span>
                                    </td>

                                    <td class="px-5 sm:px-6 py-4">
                                        <input
                                            v-model.number="detalle.cantidad"
                                            type="number"
                                            min="0.0001"
                                            step="0.0001"
                                            :max="stockDisponible(detalle)"
                                            class="form-input w-28"
                                        />
                                        <p v-if="equivalencia(detalle)" class="text-xs text-slate-500 mt-1">
                                            = {{ equivalencia(detalle) }}
                                        </p>
                                    </td>

                                    <td class="px-5 sm:px-6 py-4">
                                        <input
                                            v-model.number="detalle.precio_unitario"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-input w-36 text-right font-mono"
                                        />
                                    </td>

                                    <td class="px-5 sm:px-6 py-4 text-right font-semibold text-slate-700 whitespace-nowrap">
                                        Bs. {{ subtotal(detalle).toFixed(2) }}
                                    </td>

                                    <td class="px-5 sm:px-6 py-4 text-right">
                                        <button
                                            type="button"
                                            @click="eliminarDetalle(index)"
                                            :disabled="form.detalles.length <= 1"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-700 transition disabled:text-slate-300 disabled:hover:bg-transparent disabled:cursor-not-allowed"
                                            aria-label="Eliminar producto"
                                            title="Eliminar producto"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5 flex justify-end bg-slate-50/70 border border-slate-100 rounded-xl px-5 py-4">
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Total</p>
                            <p class="text-2xl font-bold text-slate-800">
                                Bs. {{ total().toFixed(2) }}
                            </p>
                            <p v-if="form.pago_tipo === 'credito'" class="text-sm text-amber-600 mt-1">
                                Saldo a cobrar: Bs. {{ total().toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="form.errors.detalles" class="mt-4 bg-red-50 border border-red-200 text-red-600 rounded-xl p-4 text-sm">
                        {{ form.errors.detalles }}
                    </div>
                </FormSection>

                <FormActions>
                    <Link :href="route('ventas.show', venta.id)">
                        <Button type="button" color="secondary">Cancelar</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing || !form.detalles.length">
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </Button>
                </FormActions>

                <template #aside>
                    <FormHelp title="Resumen de la venta" description="Totales en tiempo real" tone="violet">
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </template>
                        <dl class="space-y-2.5">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs text-slate-500">Subtotal</dt>
                                <dd class="text-sm font-semibold text-slate-700">Bs. {{ total().toFixed(2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs text-slate-500">Total</dt>
                                <dd class="text-sm font-bold text-slate-800">Bs. {{ total().toFixed(2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs text-slate-500">Condición</dt>
                                <dd class="text-sm font-semibold text-slate-700">
                                    {{ form.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs text-slate-500">
                                    {{ form.pago_tipo === 'credito' ? 'Saldo pendiente' : 'Monto pagado' }}
                                </dt>
                                <dd
                                    class="text-sm font-semibold"
                                    :class="form.pago_tipo === 'credito' ? 'text-amber-600' : 'text-emerald-600'"
                                >
                                    Bs. {{ total().toFixed(2) }}
                                </dd>
                            </div>
                        </dl>
                    </FormHelp>

                    <FormHelp title="Cliente y crédito" description="Datos para el cobro" tone="emerald">
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <ul class="space-y-2 text-xs leading-relaxed">
                            <li class="flex gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Verifica que el cliente y el tipo de pago coincidan con la venta original.</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Los cambios en el total actualizan el saldo pendiente del crédito.</span>
                            </li>
                        </ul>
                    </FormHelp>

                    <FormHelp title="Forma de pago" description="Contado o crédito" tone="amber">
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </template>
                        <p class="text-xs leading-relaxed">
                            Si cambias a crédito, el saldo pendiente se recalcula con el total actual. Los pagos ya registrados se mantienen.
                        </p>
                    </FormHelp>
                </template>
            </FormLayout>

        </form>

        <ProductPickerModal
            :show="selectorAbierto"
            :productos="productos"
            modo="venta"
            :seleccionado="variantePreseleccionada"
            @close="cerrarSelector"
            @select="confirmarVariante"
        />

    </div>

</AppLayout>

</template>
