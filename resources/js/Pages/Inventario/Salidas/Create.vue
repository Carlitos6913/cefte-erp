<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
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
import {
    formatearCantidad,
    formatearFechaCorta,
    presentacionVariante,
    repartirFefo,
    simboloUnidad,
} from '@/utils/producto'

const props = defineProps({ productos: Array })

const motivos = [
    { value: 'venta', label: 'Despacho para venta' },
    { value: 'dano', label: 'Daño' },
    { value: 'vencimiento', label: 'Vencimiento' },
    { value: 'degusto', label: 'Degusto / Muestra' },
    { value: 'donacion', label: 'Donación' },
    { value: 'ajuste', label: 'Ajuste de inventario' },
    { value: 'otro', label: 'Otro' },
]

const form = useForm({
    fecha: new Date().toISOString().split('T')[0],
    motivo: '',
    observaciones: '',
    detalles: [],
})

const selectorAbierto = ref(false)
const indiceEditando = ref(null)

const totalItems = computed(() => {
    return form.detalles.filter(detalle => detalle.product_variant_id).length
})

const totalCantidad = computed(() => {
    return form.detalles.reduce(
        (sum, detalle) => sum + Number(detalle.cantidad || 0),
        0
    )
})

const productoDe = (detalle) => {
    return props.productos.find(
        producto => String(producto.id) === String(detalle.product_variant_id)
    )
}

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

const confirmarVariante = (variante) => {
    if (indiceEditando.value === null) {
        form.detalles.push({
            product_variant_id: variante.id,
            cantidad: 1,
        })
    } else {
        const detalle = form.detalles[indiceEditando.value]

        if (detalle) {
            const cambio = String(detalle.product_variant_id) !== String(variante.id)

            detalle.product_variant_id = variante.id

            if (cambio) {
                detalle.cantidad = 1
            }
        }
    }

    cerrarSelector()
}

const eliminarProducto = (index) => {
    form.detalles.splice(index, 1)
}

const stockDisponible = (detalle) => {
    const producto = productoDe(detalle)
    return Number(producto?.stock ?? 0)
}

const unidadDe = (detalle) => simboloUnidad(productoDe(detalle))

const excedeStock = (detalle) => {
    return Boolean(detalle.product_variant_id)
        && Number(detalle.cantidad || 0) > stockDisponible(detalle)
}

const fefoDe = (detalle) => {
    return repartirFefo(productoDe(detalle), detalle.cantidad)
}

const registrar = () => {
    if (!form.detalles.length) return

    form.post(route('salidas.store'), { preserveScroll: true })
}
</script>

<template>
    <Head title="Nueva salida" />

    <AppLayout>
        <div class="max-w-6xl mx-auto">
            <PageHeader title="Nueva salida" subtitle="Registra la salida de productos del almacén">
                <template #icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75V21m0 0l-3.75-3.75M12 21l3.75-3.75M4.5 3.75h15M6.75 3.75v7.5a5.25 5.25 0 0010.5 0v-7.5" />
                    </svg>
                </template>
                <template #actions>
                    <Link :href="route('salidas.index')">
                        <Button color="secondary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            Volver al listado
                        </Button>
                    </Link>
                </template>
            </PageHeader>

            <form @submit.prevent="registrar">
                <FormLayout :aside="true">
                    <FormSection
                        title="Información de la salida"
                        description="Datos generales para identificar y justificar la salida."
                        tone="emerald"
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

                            <FormField label="Motivo" for-id="motivo" :error="form.errors.motivo" required>
                                <select
                                    id="motivo"
                                    v-model="form.motivo"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.motivo }"
                                >
                                    <option value="">Seleccionar motivo</option>
                                    <option v-for="m in motivos" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <p v-if="form.motivo === 'venta'" class="text-xs text-emerald-600 mt-1.5">
                                    Esta salida habilita la venta de los productos despachados.
                                </p>
                            </FormField>

                            <div class="md:col-span-2">
                                <FormField label="Observaciones" for-id="observaciones" :error="form.errors.observaciones" optional>
                                    <textarea
                                        id="observaciones"
                                        v-model="form.observaciones"
                                        rows="3"
                                        placeholder="Observaciones de la salida..."
                                        class="form-textarea"
                                        :class="{ 'is-invalid': form.errors.observaciones }"
                                    ></textarea>
                                </FormField>
                            </div>
                        </div>
                    </FormSection>

                    <FormSection
                        title="Productos"
                        description="Selecciona los productos que salen del almacén. El sistema descuenta los lotes por FEFO."
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0l-8.25 4.5m8.25-4.5v9l-8.25 4.5m0-9L3.75 7.5m8.25 4.5v9m-8.25-13.5v9l8.25 4.5" />
                            </svg>
                        </template>
                        <template #actions>
                            <Button type="button" color="secondary" @click="abrirSelector()">
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
                                title="Aún no agregaste productos"
                                :message="productos.length
                                    ? 'Selecciona la categoría, el producto y su presentación. El sistema descontará los lotes por FEFO.'
                                    : 'No hay stock disponible. Registra un ingreso de mercadería para poder dar salida a las presentaciones.'"
                            >
                                <template #action>
                                    <Button v-if="productos.length" type="button" @click="abrirSelector()">
                                        Agregar primer producto
                                    </Button>
                                    <Link v-else :href="route('ingresos.create')">
                                        <Button type="button">
                                            Registrar ingreso
                                        </Button>
                                    </Link>
                                </template>
                            </EmptyState>
                        </div>

                        <div v-else class="-mx-5 sm:-mx-6 -mt-5 sm:-mt-6 overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Producto</th>
                                        <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Stock disponible</th>
                                        <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Cantidad a salir</th>
                                        <th class="px-5 sm:px-6 py-3 text-left text-[11px] uppercase tracking-wider font-semibold text-slate-500">Lotes que salen (FEFO)</th>
                                        <th class="px-5 sm:px-6 py-3 text-right text-[11px] uppercase tracking-wider font-semibold text-slate-500">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(detalle, index) in form.detalles" :key="index" class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-5 sm:px-6 py-4 min-w-[300px]">
                                            <div v-if="productoDe(detalle)" class="flex items-start gap-3">
                                                <ProductVisual
                                                    :name="productoDe(detalle).product?.name"
                                                    :category="productoDe(detalle).product?.category?.name"
                                                    :image="productoDe(detalle).product?.image_url"
                                                    class="h-11 w-11 shrink-0 rounded-xl"
                                                />

                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-slate-800">
                                                        {{ productoDe(detalle).product?.name }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ presentacionVariante(productoDe(detalle)) || 'Presentación única' }}
                                                    </p>
                                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">
                                                        {{ productoDe(detalle).sku }}
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
                                        <td class="px-5 sm:px-6 py-4 w-36">
                                            <span
                                                class="text-sm font-medium"
                                                :class="stockDisponible(detalle) > 0 ? 'text-slate-700' : 'text-red-500'"
                                            >
                                                {{ formatearCantidad(stockDisponible(detalle)) }} {{ unidadDe(detalle) }}
                                            </span>
                                        </td>
                                        <td class="px-5 sm:px-6 py-4 w-44">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    v-model.number="detalle.cantidad"
                                                    type="number"
                                                    min="1"
                                                    :max="stockDisponible(detalle)"
                                                    class="form-input"
                                                    :class="{ 'is-invalid': excedeStock(detalle) }"
                                                />
                                                <span class="text-xs text-slate-400">{{ unidadDe(detalle) }}</span>
                                            </div>
                                            <p v-if="excedeStock(detalle)" class="text-xs text-red-500 mt-1">
                                                Supera el stock ({{ formatearCantidad(stockDisponible(detalle)) }} {{ unidadDe(detalle) }})
                                            </p>
                                        </td>
                                        <td class="px-5 sm:px-6 py-4 min-w-[220px]">
                                            <div v-if="fefoDe(detalle).asignaciones.length" class="space-y-1">
                                                <div
                                                    v-for="asignacion in fefoDe(detalle).asignaciones"
                                                    :key="asignacion.id"
                                                    class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-2 py-1"
                                                >
                                                    <span class="font-mono text-[11px] font-semibold text-slate-600">
                                                        {{ asignacion.codigo }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-400">
                                                        vence {{ formatearFechaCorta(asignacion.fecha_caducidad) }}
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-700">
                                                        {{ formatearCantidad(asignacion.cantidad_asignada) }}
                                                    </span>
                                                </div>
                                                <p v-if="fefoDe(detalle).pendiente > 0" class="text-[11px] font-medium text-red-500">
                                                    Faltan {{ formatearCantidad(fefoDe(detalle).pendiente) }} {{ unidadDe(detalle) }} sin lote disponible.
                                                </p>
                                            </div>
                                            <span v-else class="text-xs text-slate-400">Sin lotes</span>
                                        </td>
                                        <td class="px-5 sm:px-6 py-4 text-right">
                                            <button
                                                type="button"
                                                @click="eliminarProducto(index)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-700 transition"
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

                        <div v-if="form.errors.detalles" class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
                            {{ form.errors.detalles }}
                        </div>
                    </FormSection>

                    <FormActions>
                        <Link :href="route('salidas.index')">
                            <Button type="button" color="secondary">Cancelar</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing || !form.detalles.length">
                            {{ form.processing ? 'Registrando...' : 'Registrar salida' }}
                        </Button>
                    </FormActions>

                    <template #aside>
                        <FormHelp title="Resumen de la salida" description="Totales en tiempo real" tone="violet">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </template>
                            <dl class="space-y-2.5">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Productos agregados</dt>
                                    <dd class="text-sm font-semibold text-slate-700">{{ totalItems }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Cantidad total</dt>
                                    <dd class="text-sm font-semibold text-slate-700">{{ totalCantidad }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Motivo</dt>
                                    <dd class="text-sm font-semibold text-slate-700">
                                        {{ motivos.find(m => m.value === form.motivo)?.label ?? 'Sin definir' }}
                                    </dd>
                                </div>
                            </dl>
                        </FormHelp>

                        <FormHelp title="Descuento por lotes" description="Salida controlada" tone="emerald">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <p class="text-xs leading-relaxed">
                                El sistema descuenta primero los lotes con vencimiento más próximo. Verifica que la cantidad despachada coincida con la salida física.
                            </p>
                        </FormHelp>

                        <FormHelp title="Control de stock" description="Evita diferencias" tone="amber">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM10.34 3.94l-7.5 12.99A1.5 1.5 0 004.14 19.5h15.72a1.5 1.5 0 001.3-2.57l-7.5-12.99a1.5 1.5 0 00-2.6 0z" />
                                </svg>
                            </template>
                            <ul class="space-y-2 text-xs leading-relaxed">
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>La cantidad no debe superar el stock disponible del producto.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>Si el motivo es venta, podrás vender los productos despachados.</span>
                                </li>
                            </ul>
                        </FormHelp>
                    </template>
                </FormLayout>
            </form>

            <ProductPickerModal
                :show="selectorAbierto"
                :productos="productos"
                modo="salida"
                :seleccionado="variantePreseleccionada"
                @close="cerrarSelector"
                @select="confirmarVariante"
            />
        </div>
    </AppLayout>
</template>
