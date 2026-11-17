<script setup>
import { computed, ref, watch } from 'vue'

import Modal from '@/Components/Modal.vue'
import ProductVisual from '@/Components/ProductVisual.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import {
    disponibleVariante,
    formatearCantidad,
    formatearFechaCorta,
    lotesFefo,
    normalizarTexto,
    presentacionVariante,
    simboloUnidad,
} from '@/utils/producto'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    productos: {
        type: Array,
        default: () => [],
    },
    modo: {
        type: String,
        default: 'salida',
    },
    seleccionado: {
        type: [Number, String],
        default: null,
    },
})

const emit = defineEmits(['close', 'select'])

const busqueda = ref('')
const categoriaId = ref(null)
const productoId = ref(null)
const varianteId = ref(null)

const esVenta = computed(() => props.modo === 'venta')

const titulo = computed(() => (
    esVenta.value
        ? 'Agregar producto a la venta'
        : 'Agregar producto a la salida'
))

const descripcion = computed(() => (
    esVenta.value
        ? 'Busca por categoría y producto, y elige la presentación con saldo autorizado.'
        : 'Busca por categoría y producto, y elige la presentación que sale del almacén.'
))

const textoDisponible = computed(() => (
    esVenta.value ? 'saldo autorizado' : 'stock disponible'
))

const grupos = computed(() => {
    const mapa = new Map()

    for (const variante of props.productos ?? []) {
        const producto = variante.product

        if (!producto) continue

        let grupo = mapa.get(producto.id)

        if (!grupo) {
            grupo = {
                id: producto.id,
                nombre: producto.name,
                codigo: producto.code,
                imagen: producto.image_url,
                categoria: producto.category ?? null,
                variantes: [],
            }

            mapa.set(producto.id, grupo)
        }

        grupo.variantes.push(variante)
    }

    return [...mapa.values()]
        .map((grupo) => {
            const variantes = grupo.variantes
                .filter((variante) => (
                    disponibleVariante(variante, props.modo) > 0
                    || String(variante.id) === String(props.seleccionado)
                ))
                .sort((a, b) => presentacionVariante(a).localeCompare(
                    presentacionVariante(b),
                    'es'
                ))

            return { ...grupo, variantes }
        })
        .filter((grupo) => grupo.variantes.length > 0)
        .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
})

const categorias = computed(() => {
    const mapa = new Map()

    for (const grupo of grupos.value) {
        if (!grupo.categoria) continue

        let categoria = mapa.get(grupo.categoria.id)

        if (!categoria) {
            categoria = {
                id: grupo.categoria.id,
                nombre: grupo.categoria.name,
                total: 0,
            }

            mapa.set(grupo.categoria.id, categoria)
        }

        categoria.total += grupo.variantes.length
    }

    return [...mapa.values()].sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
})

const totalVariantes = computed(() => (
    grupos.value.reduce((total, grupo) => total + grupo.variantes.length, 0)
))

const sinDisponibles = computed(() => grupos.value.length === 0)

const tituloSinProductos = computed(() => {
    if (!sinDisponibles.value) return 'Sin resultados'

    return esVenta.value ? 'Sin saldo autorizado' : 'Sin stock disponible'
})

const mensajeSinProductos = computed(() => {
    if (!sinDisponibles.value) {
        return 'No hay productos que coincidan con la búsqueda o la categoría.'
    }

    return esVenta.value
        ? 'Registra una salida de despacho con motivo "venta" para habilitar presentaciones en este selector.'
        : 'Registra un ingreso de mercadería para dar stock a las presentaciones.'
})

const productosFiltrados = computed(() => {
    const termino = normalizarTexto(busqueda.value)

    return grupos.value.filter((grupo) => {
        if (categoriaId.value
            && String(grupo.categoria?.id) !== String(categoriaId.value)) {
            return false
        }

        if (!termino) return true

        const textoProducto = normalizarTexto([
            grupo.nombre,
            grupo.codigo,
            grupo.categoria?.nombre,
            grupo.categoria?.name,
        ].join(' '))

        if (textoProducto.includes(termino)) return true

        return grupo.variantes.some((variante) => normalizarTexto([
            variante.sku,
            variante.flavor,
            variante.packaging_type,
            variante.net_content,
            variante.presentacion,
        ].join(' ')).includes(termino))
    })
})

const productoActivo = computed(() => (
    productosFiltrados.value.find(
        (grupo) => String(grupo.id) === String(productoId.value)
    ) ?? null
))

const variantesProducto = computed(() => productoActivo.value?.variantes ?? [])

const varianteElegida = computed(() => (
    variantesProducto.value.find(
        (variante) => String(variante.id) === String(varianteId.value)
    ) ?? null
))

const disponible = (variante) => disponibleVariante(variante, props.modo)

const seleccionarCategoria = (id) => {
    categoriaId.value = id
    productoId.value = null
    varianteId.value = null
}

const seleccionarProducto = (grupo) => {
    productoId.value = grupo.id

    const mantieneSeleccion = grupo.variantes.some(
        (variante) => String(variante.id) === String(varianteId.value)
    )

    if (mantieneSeleccion) return

    varianteId.value = grupo.variantes.length === 1
        ? grupo.variantes[0].id
        : null
}

const seleccionarVariante = (variante) => {
    if (disponible(variante) <= 0
        && String(variante.id) !== String(varianteId.value)) {
        return
    }

    varianteId.value = variante.id
}

const cerrar = () => emit('close')

const confirmar = () => {
    if (!varianteElegida.value) return

    emit('select', varianteElegida.value)
}

const reiniciar = () => {
    busqueda.value = ''

    const inicial = (props.productos ?? []).find(
        (variante) => String(variante.id) === String(props.seleccionado)
    )

    categoriaId.value = inicial?.product?.category?.id ?? null
    productoId.value = inicial?.product?.id ?? null
    varianteId.value = inicial?.id ?? null
}

watch(
    () => props.show,
    (visible) => {
        if (visible) reiniciar()
    },
    { immediate: true }
)

watch(productosFiltrados, (lista) => {
    if (productoId.value
        && !lista.some((grupo) => String(grupo.id) === String(productoId.value))) {
        productoId.value = null
        varianteId.value = null
    }

    if (!productoId.value && lista.length === 1) {
        seleccionarProducto(lista[0])
    }
})
</script>

<template>
    <Modal :show="show" max-width="4xl" @close="cerrar">
        <div class="flex max-h-[85vh] flex-col bg-white">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ titulo }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ descripcion }}</p>
                </div>

                <button
                    type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Cerrar selector"
                    @click="cerrar"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <SearchInput
                    v-model="busqueda"
                    fluid
                    placeholder="Buscar producto, sabor, presentación o SKU..."
                />

                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                        :class="categoriaId === null
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700'"
                        @click="seleccionarCategoria(null)"
                    >
                        Todas
                        <span class="rounded-full bg-white/70 px-1.5 text-[10px]">{{ totalVariantes }}</span>
                    </button>

                    <button
                        v-for="categoria in categorias"
                        :key="categoria.id"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                        :class="String(categoriaId) === String(categoria.id)
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700'"
                        @click="seleccionarCategoria(categoria.id)"
                    >
                        {{ categoria.nombre }}
                        <span class="rounded-full bg-white/70 px-1.5 text-[10px]">{{ categoria.total }}</span>
                    </button>
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <section class="border-b border-slate-100 p-3 sm:p-4 lg:overflow-y-auto lg:border-b-0 lg:border-r">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            1. Producto
                        </p>
                        <span class="text-[11px] text-slate-400">
                            {{ productosFiltrados.length }}
                        </span>
                    </div>

                    <div v-if="productosFiltrados.length" class="space-y-1.5">
                        <button
                            v-for="grupo in productosFiltrados"
                            :key="grupo.id"
                            type="button"
                            class="flex w-full items-center gap-3 rounded-xl border p-2.5 text-left transition"
                            :class="String(productoId) === String(grupo.id)
                                ? 'border-emerald-300 bg-emerald-50/70'
                                : 'border-transparent hover:border-slate-200 hover:bg-slate-50'"
                            @click="seleccionarProducto(grupo)"
                        >
                            <ProductVisual
                                :name="grupo.nombre"
                                :category="grupo.categoria?.nombre ?? grupo.categoria?.name ?? ''"
                                :image="grupo.imagen"
                                class="h-10 w-10 shrink-0 rounded-xl"
                            />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800">
                                    {{ grupo.nombre }}
                                </p>
                                <p class="truncate text-xs text-slate-400">
                                    {{ grupo.categoria?.nombre ?? grupo.categoria?.name ?? 'Sin categoría' }}
                                    · {{ grupo.codigo }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-white px-2 py-1 text-[10px] font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                                {{ grupo.variantes.length }}
                            </span>
                        </button>
                    </div>

                    <EmptyState
                        v-else
                        :title="tituloSinProductos"
                        :message="mensajeSinProductos"
                    />
                </section>

                <section class="p-3 sm:p-4 lg:overflow-y-auto">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            2. Presentación
                        </p>
                        <span v-if="variantesProducto.length" class="text-[11px] text-slate-400">
                            {{ variantesProducto.length }}
                        </span>
                    </div>

                    <div
                        v-if="!productoActivo"
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 px-5 py-12 text-center"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l3-3m-3 3V3" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-600">
                            Selecciona un producto
                        </p>
                        <p class="mt-1 max-w-xs text-xs text-slate-400">
                            Verás aquí sus presentaciones, SKU y {{ textoDisponible }}.
                        </p>
                    </div>

                    <div v-else class="space-y-2">
                        <div class="mb-3 rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-sm font-semibold text-slate-700">{{ productoActivo.nombre }}</p>
                            <p class="text-xs text-slate-400">
                                {{ productoActivo.categoria?.nombre ?? productoActivo.categoria?.name ?? 'Sin categoría' }}
                                · {{ productoActivo.codigo }}
                            </p>
                        </div>

                        <button
                            v-for="variante in variantesProducto"
                            :key="variante.id"
                            type="button"
                            :disabled="disponible(variante) <= 0 && String(variante.id) !== String(varianteId)"
                            class="w-full rounded-xl border p-3 text-left transition"
                            :class="[
                                String(varianteId) === String(variante.id)
                                    ? 'border-emerald-400 bg-emerald-50/70 ring-1 ring-emerald-400'
                                    : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30',
                                disponible(variante) <= 0 && String(variante.id) !== String(varianteId)
                                    ? 'cursor-not-allowed opacity-60'
                                    : '',
                            ]"
                            :aria-pressed="String(varianteId) === String(variante.id)"
                            @click="seleccionarVariante(variante)"
                        >
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ presentacionVariante(variante) || 'Presentación única' }}
                                        </p>
                                        <Badge :color="disponible(variante) > 0 ? 'green' : 'red'" dot>
                                            {{ disponible(variante) > 0
                                                ? `${formatearCantidad(disponible(variante))} ${simboloUnidad(variante)}`
                                                : `Sin ${textoDisponible}` }}
                                        </Badge>
                                    </div>

                                    <p class="mt-1 font-mono text-[11px] text-slate-400">
                                        SKU {{ variante.sku }}
                                    </p>

                                    <div v-if="lotesFefo(variante).length" class="mt-2 rounded-lg bg-slate-50/80 px-2 py-1.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                            Lotes (FEFO)
                                        </p>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <span
                                                v-for="lote in lotesFefo(variante)"
                                                :key="lote.id"
                                                class="inline-flex items-center gap-1 rounded-md bg-white px-1.5 py-0.5 text-[10px] text-slate-500 ring-1 ring-inset ring-slate-200"
                                            >
                                                <span class="font-mono font-semibold text-slate-600">{{ lote.codigo }}</span>
                                                <span>· {{ formatearFechaCorta(lote.fecha_caducidad) }}</span>
                                                <span class="font-semibold text-slate-700">{{ formatearCantidad(lote.stock) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span>{{ disponible(variante) > 0 ? 'Disponible' : 'No disponible' }}</span>
                                        <span v-if="esVenta">
                                            Bs. {{ Number(variante.unit_price ?? 0).toFixed(2) }}
                                            / {{ simboloUnidad(variante) }}
                                        </span>
                                    </div>
                                </div>

                                <span
                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition"
                                    :class="String(varianteId) === String(variante.id)
                                        ? 'border-emerald-500 bg-emerald-500 text-white'
                                        : 'border-slate-200 text-transparent'"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </span>
                            </div>
                        </button>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="min-w-0">
                    <template v-if="varianteElegida">
                        <p class="truncate text-sm font-semibold text-slate-700">
                            {{ productoActivo?.nombre }}
                        </p>
                        <p class="truncate text-xs text-slate-500">
                            {{ presentacionVariante(varianteElegida) || 'Presentación única' }}
                            · {{ varianteElegida.sku }}
                        </p>
                    </template>
                    <p v-else class="text-xs text-slate-400">
                        Elige una presentación para continuar.
                    </p>
                </div>

                <div class="flex shrink-0 justify-end gap-2">
                    <Button type="button" color="secondary" @click="cerrar">
                        Cancelar
                    </Button>
                    <Button type="button" :disabled="!varianteElegida" @click="confirmar">
                        {{ esVenta ? 'Agregar a la venta' : 'Agregar a la salida' }}
                    </Button>
                </div>
            </div>
        </div>
    </Modal>
</template>
