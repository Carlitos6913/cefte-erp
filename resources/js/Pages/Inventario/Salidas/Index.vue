<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'

defineProps({ salidas: Object })

const headers = ['Producto', 'Fecha', 'Motivo', 'Cant.', 'Total', 'Registrado por', 'Estado', 'Acciones']

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

const anular = (id) => {
    if (!confirm('¿Está seguro de anular esta salida?\n\nEl stock asociado será revertido.')) return
    router.delete(route('salidas.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <Head title="Salidas" />

    <AppLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Salidas</h1>
                <p class="text-sm text-slate-500 mt-1">Registro y control de salidas de productos del almacén</p>
            </div>
            <Link :href="route('salidas.create')">
                <Button>Nueva salida</Button>
            </Link>
        </div>

        <Table :headers="headers">
            <tr v-for="salida in salidas.data" :key="salida.id" class="border-b border-slate-200 hover:bg-slate-50 transition">
                <td class="p-4">
                    <div v-for="detalle in salida.detalles" :key="detalle.id" class="font-medium text-slate-700">
                        {{ detalle.product_variant?.product?.name ?? detalle.product_variant?.sku ?? 'Producto no disponible' }}
                    </div>
                    <div class="text-xs text-slate-400 mt-1">{{ salida.numero }}</div>
                </td>
                <td class="p-4 text-slate-600">{{ formatoFecha(salida.fecha) }}</td>
                <td class="p-4">
                    <Badge :color="motivoColor(salida.motivo)">{{ motivoLabel(salida.motivo) }}</Badge>
                </td>
                <td class="p-4 text-slate-600">{{ salida.detalles_count }}</td>
                <td class="p-4">
                    <span class="font-semibold text-slate-700">Bs. {{ formatoMoneda(salida.total) }}</span>
                </td>
                <td class="p-4 text-slate-600">{{ salida.usuario?.name || '-' }}</td>
                <td class="p-4">
                    <Badge :color="salida.estado === 'activo' ? 'green' : 'red'">
                        {{ salida.estado === 'activo' ? 'Activa' : 'Anulada' }}
                    </Badge>
                </td>
                <td class="p-4">
                    <div class="flex gap-2">
                        <Link :href="route('salidas.show', salida.id)">
                            <Button color="secondary">Ver</Button>
                        </Link>
                        <Link v-if="salida.estado === 'activo'" :href="route('salidas.edit', salida.id)">
                            <Button color="info">Editar</Button>
                        </Link>
                        <Button v-if="salida.estado === 'activo'" color="danger" @click="anular(salida.id)">Anular</Button>
                    </div>
                </td>
            </tr>
            <tr v-if="salidas.data.length === 0">
                <td :colspan="headers.length" class="p-8 text-center text-slate-500">
                    No existen salidas registradas.
                </td>
            </tr>
        </Table>

        <div v-if="salidas.links && salidas.links.length > 3" class="flex justify-center gap-1 mt-6">
            <template v-for="link in salidas.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" v-html="link.label" preserve-scroll
                    class="px-3 py-2 rounded-lg border text-sm transition"
                    :class="link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
                <span v-else v-html="link.label" class="px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-300 bg-slate-50" />
            </template>
        </div>
    </AppLayout>
</template>
