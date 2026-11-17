<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import { presentacionVariante } from '@/utils/producto'

const props = defineProps({ salida: Object })

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => Number(valor).toFixed(2)

const motivoLabel = (motivo) => {
    const map = { venta: 'Despacho para venta', dano: 'Daño', vencimiento: 'Vencimiento', degusto: 'Degusto/Muestra', donacion: 'Donación', ajuste: 'Ajuste', otro: 'Otro' }
    return map[motivo] ?? motivo
}

const motivoColor = (motivo) => {
    const map = { venta: 'green', dano: 'red', vencimiento: 'orange', degusto: 'purple', donacion: 'blue', ajuste: 'gray', otro: 'gray' }
    return map[motivo] ?? 'gray'
}
</script>

<template>
    <Head :title="`Salida ${salida.numero}`" />

    <AppLayout>
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Link :href="route('salidas.index')" class="text-slate-500 hover:text-slate-700">←</Link>
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">{{ salida.numero }}</h1>
                        <Badge :color="salida.estado === 'activo' ? 'green' : 'red'">
                            {{ salida.estado === 'activo' ? 'Activa' : 'Anulada' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">Detalle de la salida registrada</p>
                </div>
                <Link :href="route('salidas.index')">
                    <Button color="secondary">Volver</Button>
                </Link>
            </div>

            <!-- Información general -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-slate-500">Número</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ salida.numero }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Fecha</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ formatoFecha(salida.fecha) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Motivo</p>
                        <div class="mt-1">
                            <Badge :color="motivoColor(salida.motivo)">{{ motivoLabel(salida.motivo) }}</Badge>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Registrado por</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ salida.usuario?.name || '-' }}</p>
                    </div>
                </div>

                <div v-if="salida.observaciones" class="mt-6 pt-6 border-t border-slate-200">
                    <p class="text-sm text-slate-500">Observaciones</p>
                    <p class="text-slate-700 mt-1">{{ salida.observaciones }}</p>
                </div>
            </div>

            <!-- Detalles -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Productos que salen</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full table-responsive">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">Producto</th>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">Lote</th>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">Caducidad</th>
                                <th class="p-4 text-left text-sm font-semibold text-slate-600">Unidad</th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">Cantidad</th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">P. Unitario</th>
                                <th class="p-4 text-right text-sm font-semibold text-slate-600">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="detalle in salida.detalles" :key="detalle.id" class="border-t border-slate-200">
                                <td data-label="Producto" class="p-4 text-slate-700">
                                    <div class="font-medium text-slate-700">
                                        {{ detalle.product_variant?.product?.name }}
                                    </div>
                                    <div
                                        v-if="presentacionVariante(detalle.product_variant)"
                                        class="text-xs text-slate-500"
                                    >
                                        {{ presentacionVariante(detalle.product_variant) }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ detalle.product_variant?.sku }}
                                    </div>
                                </td>
                                <td data-label="Lote" class="p-4 text-slate-700">{{ detalle.lote?.codigo ?? '-' }}</td>
                                <td data-label="Caducidad" class="p-4 text-slate-500">{{ formatoFecha(detalle.lote?.fecha_caducidad) }}</td>
                                <td data-label="Unidad" class="p-4 text-slate-500">{{ detalle.product_variant?.unit_measure?.name }}</td>
                                <td data-label="Cantidad" class="p-4 text-right font-semibold text-red-600">
                                    -{{ detalle.cantidad }}
                                    <span
                                        v-if="detalle.cantidad_vendida > 0"
                                        class="block text-xs text-emerald-600 font-medium"
                                    >
                                        {{ detalle.cantidad_vendida }} vendido
                                    </span>
                                </td>
                                <td data-label="P. Unitario" class="p-4 text-right">Bs. {{ formatoMoneda(detalle.precio_unitario) }}</td>
                                <td data-label="Subtotal" class="p-4 text-right font-semibold text-slate-800">Bs. {{ formatoMoneda(detalle.subtotal) }}</td>
                            </tr>
                            <tr v-if="salida.detalles.length === 0" class="table-empty">
                                <td colspan="7" class="p-8 text-center text-slate-500">Esta salida no tiene productos registrados.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200">
                                <td colspan="6" class="p-5 text-right font-semibold text-slate-600">Total</td>
                                <td class="p-5 text-right text-xl font-bold text-slate-800">Bs. {{ formatoMoneda(salida.total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
