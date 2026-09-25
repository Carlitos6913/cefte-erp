<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'

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
    detalles: [{ product_variant_id: '', cantidad: 1 }],
})

const agregarProducto = () => {
    form.detalles.push({ product_variant_id: '', cantidad: 1 })
}

const eliminarProducto = (index) => {
    if (form.detalles.length === 1) return
    form.detalles.splice(index, 1)
}

const stockDisponible = (detalle) => {
    const producto = props.productos.find(p => p.id == detalle.product_variant_id)
    return producto ? producto.stock : 0
}

const excedeStock = (detalle) => {
    return detalle.cantidad > stockDisponible(detalle)
}

const registrar = () => {
    form.post(route('salidas.store'), { preserveScroll: true })
}
</script>

<template>
    <Head title="Nueva salida" />

    <AppLayout>
        <div class="max-w-6xl mx-auto">
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <Link :href="route('salidas.index')" class="text-slate-500 hover:text-slate-700">←</Link>
                    <h1 class="text-3xl font-bold text-slate-800">Nueva salida</h1>
                </div>
                <p class="text-sm text-slate-500 mt-1">Registra la salida de productos del almacén.</p>
            </div>

            <form @submit.prevent="registrar" class="space-y-6">
                <!-- Información general -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-5">Información de la salida</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                            <input v-model="form.fecha" type="date"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="form.errors.fecha" class="text-sm text-red-600 mt-1">{{ form.errors.fecha }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Motivo *</label>
                            <select v-model="form.motivo"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Seleccionar motivo</option>
                                <option v-for="m in motivos" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                            <p v-if="form.errors.motivo" class="text-sm text-red-600 mt-1">{{ form.errors.motivo }}</p>
                            <p v-if="form.motivo === 'venta'" class="text-xs text-emerald-600 mt-1">
                                Esta salida habilita la venta de los productos despachados.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="3" placeholder="Observaciones de la salida..."
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">Productos</h2>
                            <p class="text-sm text-slate-500">Selecciona los productos que salen del almacén.</p>
                        </div>
                        <Button type="button" color="secondary" @click="agregarProducto">+ Agregar producto</Button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="p-4 text-left text-sm font-semibold">Producto</th>
                                    <th class="p-4 text-left text-sm font-semibold">Stock disponible</th>
                                    <th class="p-4 text-left text-sm font-semibold">Cantidad a salir</th>
                                    <th class="p-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(detalle, index) in form.detalles" :key="index" class="border-t border-slate-200">
                                    <td class="p-4 min-w-[280px]">
                                        <select v-model="detalle.product_variant_id"
                                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">Seleccionar producto</option>
                                            <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                                {{ producto.sku }} - {{ producto.product?.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="p-4 w-36">
                                        <span class="text-sm font-medium"
                                            :class="stockDisponible(detalle) > 0 ? 'text-slate-700' : 'text-red-500'">
                                            {{ stockDisponible(detalle) }}
                                        </span>
                                    </td>
                                    <td class="p-4 w-40">
                                        <input v-model.number="detalle.cantidad" type="number" min="1"
                                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                            :class="excedeStock(detalle) ? 'border-red-300 bg-red-50' : ''" />
                                        <p v-if="excedeStock(detalle)" class="text-xs text-red-500 mt-1">
                                            Supera el stock ({{ stockDisponible(detalle) }})
                                        </p>
                                    </td>
                                    <td class="p-4">
                                        <button type="button" @click="eliminarProducto(index)" :disabled="form.detalles.length === 1"
                                            class="text-red-500 hover:text-red-700 disabled:text-slate-300 disabled:cursor-not-allowed">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="form.errors.detalles" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
                    {{ form.errors.detalles }}
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('salidas.index')">
                        <Button type="button" color="secondary">Cancelar</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Registrando...' : 'Registrar salida' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
