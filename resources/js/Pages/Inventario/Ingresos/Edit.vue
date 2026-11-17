<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { presentacionVariante } from '@/utils/producto'

const props = defineProps({
    ingreso: Object,
    categorias: Array,
    encargados: Array,
    productos: Array,
})

const form = useForm({
    fecha: props.ingreso.fecha,
    encargado_id: props.ingreso.encargado_id ?? '',
    observaciones: props.ingreso.observaciones ?? '',

    categoria_id: '',
})

const varianteInicial = props.productos.find(
    p => p.id === props.ingreso.detalles[0]?.product_variant_id
)

form.categoria_id = varianteInicial?.product?.category_id ?? ''

const productosPorCategoria = computed(() => {

    if (!form.categoria_id) {
        return []
    }

    return props.productos.filter(
        producto => producto.product?.category_id == form.categoria_id
    )

})

const nombreVariante = (variante) => {
    const presentacion = presentacionVariante(variante)

    return `${variante.product?.name ?? variante.sku}${presentacion ? ` - ${presentacion}` : ''} (${variante.sku})`
}

const productoSeleccionado = (detalle) => {
    return props.productos.find(
        producto => producto.id == detalle.product_variant_id
    )
}

/**
 * Unidades en las que se puede capturar la cantidad de la variante
 * elegida: su unidad base y, si tiene empaque configurado, la caja.
 */
const unidadesDe = (detalle) => {
    return productoSeleccionado(detalle)?.unidades ?? []
}

/**
 * Cuántas unidades base representa una unidad del selector.
 */
const factorDe = (detalle) => {
    return factorDeVariant(detalle.product_variant_id, detalle.unidad_id)
}

const factorDeVariant = (variantId, unidadId) => {
    const producto = props.productos.find(
        producto => producto.id == variantId
    )

    const unidad = producto?.unidades?.find(
        item => item.id == unidadId
    )

    return Number(unidad?.factor ?? 1)
}

/**
 * El catálogo siempre cotiza por unidad base. Si el usuario elige la
 * caja, el precio de referencia se multiplica por el factor para que
 * el subtotal no cambie.
 */
const actualizarPrecio = (detalle) => {
    const producto = productoSeleccionado(detalle)

    if (!producto) {
        return
    }

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

const cambiarCategoria = () => {

    form.detalles.forEach(detalle => {
        detalle.product_variant_id = ''
        detalle.unidad_id = ''
        detalle.precio_unitario = 0
    })

}

const agregarDetalle = () => {
    form.detalles.push({
        product_variant_id: '',
        lote: '',
        fecha_caducidad: '',
        unidad_id: '',
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

const subtotal = (detalle) => {
    return Number(detalle.cantidad || 0)
        * Number(detalle.precio_unitario || 0)
}

/*
 * Los detalles existentes guardan `cantidad` en unidad base, pero el
 * formulario los presenta como los capturó el usuario: "3 cajas" con
 * su precio por caja.
 */
form.detalles = props.ingreso.detalles.map(detalle => {

    const unidadId = detalle.unidad_id
        ?? props.productos.find(
            p => p.id === detalle.product_variant_id
        )?.unidad_base_id
        ?? ''

    return {
        product_variant_id: detalle.product_variant_id,
        lote: detalle.lote?.codigo ?? '',
        fecha_caducidad: detalle.lote?.fecha_caducidad
            ? String(detalle.lote.fecha_caducidad).substring(0, 10)
            : '',
        unidad_id: unidadId,
        cantidad: Number(detalle.cantidad_original ?? detalle.cantidad),
        precio_unitario: Number(
            (Number(detalle.precio_unitario ?? 0)
                * factorDeVariant(detalle.product_variant_id, unidadId)
            ).toFixed(2)
        ),
    }

})

const total = () => {
    return form.detalles.reduce(
        (sum, detalle) => sum + subtotal(detalle),
        0
    )
}

const cantidadTotal = computed(() => {
    return form.detalles.reduce(
        (suma, detalle) => suma + Number(detalle.cantidad || 0),
        0
    )
})

const actualizar = () => {

    form.put(route('ingresos.update', props.ingreso.id), {
        preserveScroll: true,
    })

}

</script>


<template>

<Head title="Editar ingreso" />

<AppLayout>

    <div class="max-w-7xl mx-auto">

        <PageHeader
            title="Editar ingreso"
            :subtitle="ingreso.numero"
        >
            <template #actions>
                <Link :href="route('ingresos.show', ingreso.id)">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al detalle
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <form @submit.prevent="actualizar" class="space-y-5">

            <FormLayout aside>

                <FormSection
                    title="Información del ingreso"
                    description="Actualiza los datos generales del documento."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2.25-8.25V19.5a2.25 2.25 0 01-2.25 2.25H9a2.25 2.25 0 01-2.25-2.25V4.5A2.25 2.25 0 019 2.25h4.5A2.25 2.25 0 0115.75 4.5v2.25" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <FormField
                            label="Fecha"
                            for-id="fecha"
                            :error="form.errors.fecha"
                            required
                        >
                            <input
                                id="fecha"
                                v-model="form.fecha"
                                type="date"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.fecha }"
                            />
                        </FormField>

                        <FormField
                            label="Encargado de la recepción"
                            for-id="encargado_id"
                            :error="form.errors.encargado_id"
                            required
                        >
                            <select
                                id="encargado_id"
                                v-model="form.encargado_id"
                                class="form-select"
                                :class="{ 'is-invalid': form.errors.encargado_id }"
                            >
                                <option value="">
                                    Seleccionar encargado
                                </option>
                                <option
                                    v-for="encargado in encargados"
                                    :key="encargado.id"
                                    :value="encargado.id"
                                >
                                    {{ encargado.first_name }}
                                    {{ encargado.first_surname }}
                                    <span v-if="encargado.second_surname">
                                        {{ encargado.second_surname }},
                                    </span>
                                    — {{ encargado.identity_card }}
                                </option>
                            </select>
                        </FormField>

                        <FormField
                            label="Número"
                            for-id="numero"
                            hint="El número no puede modificarse."
                        >
                            <input
                                id="numero"
                                :value="ingreso.numero"
                                type="text"
                                disabled
                                readonly
                                class="form-input font-mono"
                            />
                        </FormField>

                        <div class="md:col-span-3">
                            <FormField
                                label="Observaciones"
                                for-id="observaciones"
                                :error="form.errors.observaciones"
                                optional
                            >
                                <textarea
                                    id="observaciones"
                                    v-model="form.observaciones"
                                    rows="3"
                                    class="form-textarea"
                                    :class="{ 'is-invalid': form.errors.observaciones }"
                                    placeholder="Observaciones del ingreso..."
                                ></textarea>
                            </FormField>
                        </div>

                    </div>
                </FormSection>

                <FormSection
                    title="Productos"
                    description="Modifica las cantidades, productos o precios. Puedes capturarlos por unidad de venta o por cajas."
                    tone="violet"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m11.142-4.5L21.75 12l-4.179 2.25M6.429 9.75L12 6l5.571 3.75M6.429 9.75v4.5L12 18l5.571-3.75v-4.5M12 18v3.75" />
                        </svg>
                    </template>

                    <template #actions>
                        <Button
                            type="button"
                            color="secondary"
                            size="sm"
                            @click="agregarDetalle"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Agregar producto
                        </Button>
                    </template>

                    <div class="max-w-md">
                        <FormField
                            label="Categoría"
                            for-id="categoria_id"
                            hint="Selecciona una categoría para cargar sus productos."
                        >
                            <select
                                id="categoria_id"
                                v-model="form.categoria_id"
                                @change="cambiarCategoria"
                                class="form-select"
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
                        </FormField>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">

                        <table class="w-full min-w-[960px]">

                            <thead class="bg-slate-50">

                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Producto
                                    </th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Lote
                                    </th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Caducidad
                                    </th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Unidad
                                    </th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Cantidad
                                    </th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Precio unitario
                                    </th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Subtotal
                                    </th>
                                    <th class="px-4 py-3"></th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">

                                <tr
                                    v-for="(detalle, index) in form.detalles"
                                    :key="index"
                                >
                                    <td class="px-4 py-3 align-top min-w-[260px]">
                                        <select
                                            v-model="detalle.product_variant_id"
                                            @change="actualizarPrecio(detalle)"
                                            class="form-select px-3 py-2"
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
                                                {{ nombreVariante(producto) }}
                                            </option>
                                        </select>
                                    </td>

                                    <td class="px-4 py-3 align-top w-32">
                                        <input
                                            v-model="detalle.lote"
                                            type="text"
                                            maxlength="50"
                                            class="form-input px-3 py-2 font-mono"
                                        />
                                    </td>

                                    <td class="px-4 py-3 align-top w-40">
                                        <input
                                            v-model="detalle.fecha_caducidad"
                                            type="date"
                                            class="form-input px-3 py-2"
                                        />
                                    </td>

                                    <td class="px-4 py-3 align-top w-40">
                                        <select
                                            v-model="detalle.unidad_id"
                                            @change="cambiarUnidad(detalle)"
                                            :disabled="!detalle.product_variant_id || unidadesDe(detalle).length < 2"
                                            class="form-select px-3 py-2"
                                        >
                                            <option
                                                v-for="unidad in unidadesDe(detalle)"
                                                :key="unidad.id"
                                                :value="unidad.id"
                                            >
                                                {{ unidad.name }}
                                                <span v-if="unidad.factor > 1">
                                                    ({{ unidad.factor }}
                                                    {{ unidadesDe(detalle)[0]?.symbol }})
                                                </span>
                                            </option>
                                        </select>
                                    </td>

                                    <td class="px-4 py-3 align-top w-36">
                                        <input
                                            v-model.number="detalle.cantidad"
                                            type="number"
                                            min="0.0001"
                                            step="0.0001"
                                            class="form-input px-3 py-2 text-right"
                                        />
                                        <p
                                            v-if="equivalencia(detalle)"
                                            class="text-xs text-slate-400 mt-1 text-right"
                                        >
                                            = {{ equivalencia(detalle) }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-3 align-top w-40">
                                        <input
                                            v-model.number="detalle.precio_unitario"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-input px-3 py-2 text-right font-mono"
                                        />
                                    </td>

                                    <td class="px-4 py-3 align-top text-right font-semibold text-slate-700 whitespace-nowrap">
                                        Bs. {{ subtotal(detalle).toFixed(2) }}
                                    </td>

                                    <td class="px-4 py-3 align-top text-right">
                                        <button
                                            type="button"
                                            @click="eliminarDetalle(index)"
                                            :disabled="form.detalles.length <= 1"
                                            title="Eliminar producto"
                                            aria-label="Eliminar producto"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-400">
                            {{ form.detalles.length }} línea(s) registrada(s)
                        </p>
                        <div class="flex items-baseline gap-2 sm:justify-end">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                Total del ingreso
                            </span>
                            <span class="text-xl font-bold text-slate-800">
                                Bs. {{ total().toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="form.errors.detalles"
                        class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                    >
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ form.errors.detalles }}</span>
                    </div>

                </FormSection>

                <template #aside>

                    <FormHelp
                        title="Resumen del ingreso"
                        description="Totales calculados en tiempo real."
                        tone="violet"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </template>

                        <dl class="space-y-2.5">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-400">Líneas</dt>
                                <dd class="font-semibold text-slate-700">{{ form.detalles.length }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-400">Cantidad total</dt>
                                <dd class="font-semibold text-slate-700">
                                    {{ cantidadTotal.toLocaleString('es-BO', { maximumFractionDigits: 4 }) }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-2.5">
                                <dt class="text-slate-500">Monto total</dt>
                                <dd class="text-base font-bold text-slate-800">Bs. {{ total().toFixed(2) }}</dd>
                            </div>
                        </dl>
                    </FormHelp>

                    <FormHelp
                        title="Lotes y caducidad"
                        description="Trazabilidad del producto recibido."
                        tone="amber"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </template>

                        <p class="mb-2">
                            Mantén el lote y la fecha de caducidad de cada línea para conservar la trazabilidad.
                            Si actualizas una cantidad, verifica que el lote siga correspondiendo al producto.
                        </p>
                        <p>
                            Evita mezclar lotes con fechas distintas en una misma línea.
                        </p>
                    </FormHelp>

                    <FormHelp
                        title="Unidades y precios"
                        description="Captura flexible por unidad o caja."
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                            </svg>
                        </template>

                        <p class="mb-2">
                            La cantidad puede capturarse por unidad base o por caja.
                            El sistema convierte automáticamente todo a unidades de stock.
                        </p>
                        <p>
                            Al cambiar la unidad, el precio de referencia se ajusta según el factor del empaque.
                        </p>
                    </FormHelp>

                </template>

            </FormLayout>

            <FormActions>

                <Link :href="route('ingresos.show', ingreso.id)">
                    <Button type="button" color="secondary">
                        Cancelar
                    </Button>
                </Link>

                <Button type="submit" :disabled="form.processing">
                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                </Button>

            </FormActions>

        </form>

    </div>

</AppLayout>

</template>
