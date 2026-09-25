<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ salida: Object, productos: Array })

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
    fecha: props.salida.fecha,
    motivo: props.salida.motivo,
    observaciones: props.salida.observaciones ?? '',
    detalles: props.salida.detalles.map(d => ({
        product_variant_id: d.product_variant_id,
        cantidad: d.cantidad,
    })),
})

const agregarDetalle = () => {
    form.detalles.push({ product_variant_id: '', cantidad: 1 })
}

const eliminarDetalle = (index) => {
    if (form.detalles.length <= 1) return
    form.detalles.splice(index, 1)
}

const stockDisponible = (detalle) => {
    const producto = props.productos.find(p => p.id == detalle.product_variant_id)
    return producto ? producto.stock : 0
}

const excedeStock = (detalle) => {
    return detalle.cantidad > stockDisponible(detalle)
}

const actualizar = () => {
    form.put(route('salidas.update', props.salida.id), { preserveScroll: true })
}
</script>

<template>
    <Head title="Editar salida" />

    <AppLayout>
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Editar salida</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ salida.numero }}</p>
                </div>
                <Link :href="route('salidas.show', salida.id)">
                    <Button color="secondary">Cancelar</Button>
                </Link>
            </div>
        </div>

        <!-- Información general -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-5">Información de la salida</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                    <input v-model="form.fecha" type="date"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500" />
                    <p v-if="form.errors.fecha" class="text-sm text-red-600 mt-1">{{ form.errors.fecha }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Motivo</label>
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

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Número</label>
                    <input :value="salida.numero" type="text" disabled
                        class="w-full rounded-lg border-slate-200 bg-slate-100 text-slate-500" />
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones</label>
                <textarea v-model="form.observaciones" rows="3"
                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="Observaciones de la salida..."></textarea>
            </div>
        </div>

        <!-- Productos -->
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Productos</h2>
                    <p class="text-sm text-slate-500">Modifica las cantidades o productos.</p>
                </div>
                <Button color="info" @click="agregarDetalle">+ Agregar producto</Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left p-3 text-sm font-semibold text-slate-600">Producto</th>
                            <th class="text-left p-3 text-sm font-semibold text-slate-600">Stock disp.</th>
                            <th class="text-left p-3 text-sm font-semibold text-slate-600">Cantidad</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(detalle, index) in form.detalles" :key="index" class="border-b border-slate-100">
                            <td class="p-3">
                                <select v-model="detalle.product_variant_id"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="" disabled>Seleccionar producto</option>
                                    <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                        {{ producto.sku }} - {{ producto.product?.name }}
                                    </option>
                                </select>
                            </td>
                            <td class="p-3">
                                <span class="text-sm font-medium"
                                    :class="stockDisponible(detalle) > 0 ? 'text-slate-700' : 'text-red-500'">
                                    {{ stockDisponible(detalle) }}
                                </span>
                            </td>
                            <td class="p-3">
                                <input v-model.number="detalle.cantidad" type="number" min="1"
                                    class="w-28 rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                                    :class="excedeStock(detalle) ? 'border-red-300 bg-red-50' : ''" />
                                <p v-if="excedeStock(detalle)" class="text-xs text-red-500 mt-1">
                                    Supera stock ({{ stockDisponible(detalle) }})
                                </p>
                            </td>
                            <td class="p-3 text-right">
                                <button type="button" @click="eliminarDetalle(index)" :disabled="form.detalles.length <= 1"
                                    class="text-red-500 hover:text-red-700 disabled:text-slate-300">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="form.errors.detalles" class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                {{ form.errors.detalles }}
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200">
                <Link :href="route('salidas.show', salida.id)">
                    <Button color="secondary">Cancelar</Button>
                </Link>
                <Button :disabled="form.processing" @click="actualizar">
                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
