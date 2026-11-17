<script setup>
import { computed } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import Table from '@/Components/UI/Table.vue'
import MetricCard from '@/Components/Reports/MetricCard.vue'
import SectionCard from '@/Components/Reports/SectionCard.vue'
import { formatearFecha, formatearMoneda, formatearNumero } from '@/utils/informes'

const props = defineProps({
    resumen: Object,
    inventario: Object,
})

const maxCategoria = computed(() => Math.max(
    1,
    ...(props.inventario?.categorias ?? []).map((categoria) => Number(categoria.valor ?? 0))
))

const maxProductoValor = computed(() => Math.max(
    1,
    ...(props.inventario?.productos_valor ?? []).map((producto) => Number(producto.valor ?? 0))
))

const anchoBarra = (monto, maximo) => {
    return `${Math.max(4, Math.round((Number(monto ?? 0) / maximo) * 100))}%`
}

const colorLote = (lote) => {
    if (lote.vencido) return 'red'
    if (lote.dias !== null && lote.dias <= 30) return 'amber'
    return 'green'
}

const etiquetaLote = (lote) => {
    if (lote.vencido) return `Vencido hace ${Math.abs(lote.dias)} d`
    if (lote.dias === 0) return 'Vence hoy'
    return `En ${lote.dias} d`
}
</script>

<template>
    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <MetricCard
                label="Valor de inventario"
                :value="formatearMoneda(inventario.resumen.valor)"
                :hint="`Valor comercial ${formatearMoneda(inventario.resumen.valor_comercial)}`"
                tono="emerald"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Stock disponible"
                :value="formatearNumero(inventario.resumen.stock_total)"
                :hint="`${formatearNumero(inventario.resumen.presentaciones)} presentaciones · ${formatearNumero(inventario.resumen.productos)} productos`"
                tono="blue"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Presentaciones con stock bajo"
                :value="formatearNumero(inventario.resumen.stock_bajo)"
                hint="Stock igual o inferior al mínimo"
                tono="amber"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                label="Lotes por vencer"
                :value="formatearNumero(inventario.resumen.lotes_por_vencer)"
                :hint="`${formatearNumero(inventario.resumen.lotes_vencidos)} lotes ya vencidos`"
                tono="red"
            >
                <template #icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ingresos del periodo</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">{{ formatearNumero(inventario.movimientos.ingresos) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ formatearNumero(inventario.movimientos.unidades_ingresadas) }} unidades</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Salidas del periodo</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">{{ formatearNumero(inventario.movimientos.salidas) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ formatearNumero(inventario.movimientos.unidades_despachadas) }} unidades</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Categorías activas</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">{{ formatearNumero(inventario.categorias.length) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">Con stock registrado</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Costo promedio</p>
                <p class="mt-2 text-lg font-extrabold text-slate-800">
                    {{ inventario.resumen.stock_total > 0
                        ? formatearMoneda(inventario.resumen.valor / inventario.resumen.stock_total)
                        : formatearMoneda(0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">Sobre el stock disponible</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <SectionCard title="Inventario por categoría" subtitle="Valor al costo por categoría">
                <div v-if="inventario.categorias.length" class="space-y-4">
                    <div v-for="categoria in inventario.categorias" :key="categoria.categoria">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-600">{{ categoria.categoria }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatearNumero(categoria.presentaciones) }} presentaciones · {{ formatearNumero(categoria.stock) }} unidades</p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-slate-800">{{ formatearMoneda(categoria.valor) }}</p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500"
                                :style="{ width: anchoBarra(categoria.valor, maxCategoria) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin inventario disponible.</p>
            </SectionCard>

            <SectionCard title="Mayor valor en inventario" subtitle="Top 10 presentaciones por valor al costo">
                <div v-if="inventario.productos_valor.length" class="space-y-4">
                    <div v-for="(producto, indice) in inventario.productos_valor" :key="producto.id">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500">
                                    {{ indice + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-700">{{ producto.producto }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ producto.presentacion || producto.sku }}</p>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-slate-800">{{ formatearMoneda(producto.valor) }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatearNumero(producto.stock) }} {{ producto.unidad }}</p>
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-blue-400 to-indigo-500"
                                :style="{ width: anchoBarra(producto.valor, maxProductoValor) }"
                            />
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-slate-400">Sin productos con stock.</p>
            </SectionCard>
        </div>

        <div>
            <div class="mb-3 px-1">
                <h2 class="text-sm font-bold text-slate-700">Stock bajo</h2>
                <p class="mt-0.5 text-xs text-slate-400">Presentaciones en o por debajo del stock mínimo configurado</p>
            </div>

            <Table :headers="['Producto', 'Presentación', 'Stock', 'Mínimo', 'Faltante', 'Valor al costo']">
                <tr
                    v-for="producto in inventario.stock_bajo"
                    :key="producto.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/70"
                >
                    <td data-label="Producto" class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ producto.producto }}</p>
                        <p class="text-xs text-slate-400">{{ producto.categoria }}</p>
                    </td>
                    <td data-label="Presentación" class="px-5 py-4 text-slate-600">
                        {{ producto.presentacion || producto.sku }}
                    </td>
                    <td data-label="Stock" class="px-5 py-4">
                        <span class="font-bold" :class="producto.stock <= 0 ? 'text-red-600' : 'text-amber-600'">
                            {{ formatearNumero(producto.stock) }} {{ producto.unidad }}
                        </span>
                    </td>
                    <td data-label="Mínimo" class="px-5 py-4 text-slate-600">
                        {{ formatearNumero(producto.minimo) }} {{ producto.unidad }}
                    </td>
                    <td data-label="Faltante" class="px-5 py-4">
                        <Badge :color="producto.stock <= 0 ? 'red' : 'amber'">
                            {{ producto.stock <= 0 ? 'Sin stock' : `${formatearNumero(producto.faltante)} ${producto.unidad}` }}
                        </Badge>
                    </td>
                    <td data-label="Valor al costo" class="px-5 py-4 font-semibold text-slate-700">
                        {{ formatearMoneda(producto.valor) }}
                    </td>
                </tr>

                <tr v-if="!inventario.stock_bajo.length" class="table-empty">
                    <td colspan="6">
                        <EmptyState
                            title="Sin alertas de stock"
                            message="Todas las presentaciones activas se encuentran por encima de su stock mínimo."
                        />
                    </td>
                </tr>
            </Table>
        </div>

        <div>
            <div class="mb-3 px-1">
                <h2 class="text-sm font-bold text-slate-700">Vencimientos próximos</h2>
                <p class="mt-0.5 text-xs text-slate-400">Lotes con stock y caducidad dentro de los próximos 90 días o ya vencidos</p>
            </div>

            <Table :headers="['Producto', 'Lote', 'Caducidad', 'Días', 'Stock', 'Estado']">
                <tr
                    v-for="lote in inventario.lotes"
                    :key="lote.id"
                    class="border-b border-slate-100 transition-colors last:border-0 hover:bg-slate-50/70"
                >
                    <td data-label="Producto" class="px-5 py-4">
                        <p class="font-semibold text-slate-800">{{ lote.producto }}</p>
                        <p class="text-xs text-slate-400">{{ lote.presentacion || lote.sku }}</p>
                    </td>
                    <td data-label="Lote" class="px-5 py-4 font-mono text-xs text-slate-600">
                        {{ lote.lote }}
                    </td>
                    <td data-label="Caducidad" class="px-5 py-4 text-slate-600">
                        {{ formatearFecha(lote.fecha_caducidad) }}
                    </td>
                    <td data-label="Días" class="px-5 py-4 text-slate-600">
                        {{ lote.dias === null ? '-' : formatearNumero(lote.dias) }}
                    </td>
                    <td data-label="Stock" class="px-5 py-4 font-semibold text-slate-700">
                        {{ formatearNumero(lote.stock) }} {{ lote.unidad }}
                    </td>
                    <td data-label="Estado" class="px-5 py-4">
                        <Badge :color="colorLote(lote)" dot>
                            {{ etiquetaLote(lote) }}
                        </Badge>
                    </td>
                </tr>

                <tr v-if="!inventario.lotes.length" class="table-empty">
                    <td colspan="6">
                        <EmptyState
                            title="Sin vencimientos próximos"
                            message="No hay lotes con stock que caduquen en los próximos 90 días."
                        />
                    </td>
                </tr>
            </Table>
        </div>
    </div>
</template>
