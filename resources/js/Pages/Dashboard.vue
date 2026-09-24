<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    ventasHoy: Number,
    cuentasPorCobrar: Number,
    totalProductos: Number,
    stockBajo: Number,
    ventasRecientes: Array,
    ingresosRecientes: Array,
    salidasRecientes: Array,
})

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const listaProductos = (detalles) => {
    if (!detalles || !detalles.length) return '-'
    return detalles
        .map(d => {
            const nombre = d.product_variant?.product?.name ?? 'Producto'
            return d.cantidad > 1 ? `${nombre} (x${d.cantidad})` : nombre
        })
        .join(', ')
}
</script>

<template>
    <AppLayout>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

            <div class="relative bg-white rounded-2xl border border-slate-200 p-6 overflow-hidden group hover:shadow-md transition-shadow duration-200">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-full opacity-60" />
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Hoy</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-1">Ventas del día</p>
                    <p class="text-2xl font-extrabold text-slate-800 tracking-tight">Bs. {{ formatoMoneda(ventasHoy) }}</p>
                </div>
            </div>

            <div class="relative bg-white rounded-2xl border border-slate-200 p-6 overflow-hidden group hover:shadow-md transition-shadow duration-200">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-full opacity-60" />
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span v-if="cuentasPorCobrar > 0" class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Pendiente</span>
                        <span v-else class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full">Al día</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-1">Cuentas por cobrar</p>
                    <p class="text-2xl font-extrabold tracking-tight" :class="cuentasPorCobrar > 0 ? 'text-amber-600' : 'text-slate-800'">Bs. {{ formatoMoneda(cuentasPorCobrar) }}</p>
                </div>
            </div>

            <div class="relative bg-white rounded-2xl border border-slate-200 p-6 overflow-hidden group hover:shadow-md transition-shadow duration-200">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full opacity-60" />
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Inventario</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-1">Productos registrados</p>
                    <p class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ totalProductos }}</p>
                </div>
            </div>

            <div class="relative bg-white rounded-2xl border border-slate-200 p-6 overflow-hidden group hover:shadow-md transition-shadow duration-200">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-red-50 to-transparent rounded-bl-full opacity-60" />
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <span v-if="stockBajo > 0" class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Alerta</span>
                        <span v-else class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full">OK</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-1">Stock bajo</p>
                    <p class="text-2xl font-extrabold tracking-tight" :class="stockBajo > 0 ? 'text-red-600' : 'text-slate-800'">{{ stockBajo }}</p>
                </div>
            </div>

        </div>

        <!-- Tablas -->
        <div class="space-y-5 mt-6">

            <!-- Ventas recientes -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Ventas recientes</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Últimas operaciones registradas</p>
                        </div>
                        <Link href="/ventas" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                            Ver todas
                        </Link>
                    </div>
                </div>

                <div v-if="ventasRecientes.length">
                    <table class="table-auto w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Nro.</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Cliente</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Productos</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Total</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="venta in ventasRecientes"
                                :key="venta.id"
                                class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">{{ venta.numero }}</td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ formatoFecha(venta.fecha) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ venta.clienteRelacion?.nombre ?? venta.cliente ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-400 max-w-[200px] truncate" :title="listaProductos(venta.detalles)">{{ listaProductos(venta.detalles) }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">Bs. {{ formatoMoneda(venta.total) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Badge :color="venta.pago_tipo === 'credito' ? 'yellow' : 'green'" class="text-[11px]">
                                        {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-400">No existen ventas registradas.</p>
                </div>

            </div>

            <!-- Ingresos recientes -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Ingresos recientes</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Últimos ingresos al almacén</p>
                        </div>
                        <Link href="/ingresos" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                            Ver todos
                        </Link>
                    </div>
                </div>

                <div v-if="ingresosRecientes.length">
                    <table class="table-auto w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Nro.</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Productos</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Registrado por</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ingreso in ingresosRecientes"
                                :key="ingreso.id"
                                class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">{{ ingreso.numero }}</td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ formatoFecha(ingreso.fecha) }}</td>
                                <td class="px-4 py-3 text-slate-400 max-w-[200px] truncate" :title="listaProductos(ingreso.detalles)">{{ listaProductos(ingreso.detalles) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ ingreso.usuario?.name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ ingreso.observaciones || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-400">No existen ingresos registrados.</p>
                </div>

            </div>

            <!-- Salidas recientes -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Salidas recientes</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Últimas salidas del almacén</p>
                        </div>
                        <Link href="/salidas" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                            Ver todas
                        </Link>
                    </div>
                </div>

                <div v-if="salidasRecientes.length">
                    <table class="table-auto w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Nro.</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Motivo</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Productos</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="salida in salidasRecientes"
                                :key="salida.id"
                                class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">{{ salida.numero }}</td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ formatoFecha(salida.fecha) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Badge :color="salida.motivo === 'dano' ? 'red' : salida.motivo === 'vencimiento' ? 'orange' : 'gray'" class="text-[11px]">
                                        {{ salida.motivo === 'dano' ? 'Daño' : salida.motivo === 'vencimiento' ? 'Vencimiento' : salida.motivo === 'degusto' ? 'Degusto' : salida.motivo === 'donacion' ? 'Donación' : salida.motivo }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-slate-400 max-w-[200px] truncate" :title="listaProductos(salida.detalles)">{{ listaProductos(salida.detalles) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ salida.usuario?.name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-400">No existen salidas registradas.</p>
                </div>

            </div>

        </div>

    </AppLayout>
</template>
