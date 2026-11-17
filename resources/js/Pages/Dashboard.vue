<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/UI/Badge.vue'
import { capitalizar, formatearCantidad, presentacionVariante, simboloUnidad } from '@/utils/producto'

const props = defineProps({
    ventasHoy: { type: Number, default: 0 },
    ventasHoyCantidad: { type: Number, default: 0 },
    ventasAyer: { type: Number, default: 0 },
    ventasMes: { type: Number, default: 0 },
    ventasMesCantidad: { type: Number, default: 0 },
    ventasMesAnterior: { type: Number, default: 0 },
    ticketPromedio: { type: Number, default: 0 },
    cuentasPorCobrar: { type: Number, default: 0 },
    ventasCreditoPendientes: { type: Number, default: 0 },
    totalProductos: { type: Number, default: 0 },
    totalPresentaciones: { type: Number, default: 0 },
    stockBajo: { type: Number, default: 0 },
    stockCritico: { type: Array, default: () => [] },
    porVencer: { type: Array, default: () => [] },
    porVencerTotal: { type: Number, default: 0 },
    ventasRecientes: { type: Array, default: () => [] },
    ingresosRecientes: { type: Array, default: () => [] },
    salidasRecientes: { type: Array, default: () => [] },
    ventasUltimos7Dias: { type: Array, default: () => [] },
    topProductos: { type: Array, default: () => [] },
})

const page = usePage()
const usuario = computed(() => page.props.auth.user)

const modulosUsuario = computed(() => {
    const rol = usuario.value?.roles?.[0]
    if (!rol?.modulos) return null
    if (Array.isArray(rol.modulos)) return rol.modulos
    try {
        return JSON.parse(rol.modulos)
    } catch {
        return null
    }
})

const tieneAcceso = (modulo) => {
    if (modulosUsuario.value === null) return true
    return modulosUsuario.value.includes(modulo)
}

const nombreUsuario = computed(() => usuario.value?.name?.split(' ')[0] ?? 'usuario')

const saludo = computed(() => {
    const hora = new Date().getHours()
    if (hora < 12) return 'Buenos días'
    if (hora < 19) return 'Buenas tardes'
    return 'Buenas noches'
})

const fechaLarga = computed(() => {
    const fecha = new Date().toLocaleDateString('es-BO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })

    return capitalizar(fecha)
})

const formatoMoneda = (valor) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(valor ?? 0))
}

const plural = (cantidad, singular, pluralForma) => {
    return `${cantidad} ${cantidad === 1 ? singular : pluralForma}`
}

const fechaLocal = (fecha) => {
    if (!fecha) return null
    const [year, month, day] = String(fecha).slice(0, 10).split('-').map(Number)
    if (!year || !month || !day) return null
    return new Date(year, month - 1, day)
}

const formatoFecha = (fecha) => {
    const valor = fechaLocal(fecha)
    return valor ? valor.toLocaleDateString('es-BO') : '-'
}

const formatoFechaCorta = (fecha) => {
    const valor = fechaLocal(fecha)
    if (!valor) return '-'
    return valor.toLocaleDateString('es-BO', { day: '2-digit', month: 'short' }).replace('.', '')
}

const fechaHoy = () => {
    const fecha = new Date()
    fecha.setHours(0, 0, 0, 0)
    return fecha
}

const diasParaVencer = (fecha) => {
    const valor = fechaLocal(fecha)
    if (!valor) return null
    return Math.round((valor - fechaHoy()) / 86400000)
}

const etiquetaVencimiento = (fecha) => {
    const dias = diasParaVencer(fecha)
    if (dias === null) return 'Sin fecha'
    if (dias < 0) return `Vencido hace ${Math.abs(dias)} d`
    if (dias === 0) return 'Vence hoy'
    return `En ${dias} d`
}

const tonoVencimiento = (fecha) => {
    const dias = diasParaVencer(fecha)
    if (dias === null) return 'slate'
    if (dias <= 0) return 'red'
    if (dias <= 7) return 'amber'
    return 'slate'
}

const textoVariacionHoy = computed(() => {
    if (props.ventasAyer <= 0) {
        return props.ventasHoy > 0 ? 'sin ventas ayer' : 'aún sin movimiento'
    }

    const cambio = ((props.ventasHoy - props.ventasAyer) / props.ventasAyer) * 100
    return `${cambio >= 0 ? '+' : ''}${cambio.toFixed(0)}% vs ayer`
})

const variacionMes = computed(() => {
    if (props.ventasMesAnterior <= 0) return null
    return ((props.ventasMes - props.ventasMesAnterior) / props.ventasMesAnterior) * 100
})

const badgeMes = computed(() => {
    if (variacionMes.value === null) return 'Mes actual'
    return `${variacionMes.value >= 0 ? '+' : ''}${variacionMes.value.toFixed(0)}%`
})

const tonosBadge = {
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    amber: 'bg-amber-50 text-amber-700 ring-amber-200',
    red: 'bg-red-50 text-red-700 ring-red-200',
    blue: 'bg-blue-50 text-blue-700 ring-blue-200',
    slate: 'bg-slate-50 text-slate-600 ring-slate-200',
}

const acentos = {
    emerald: {
        barra: 'from-emerald-400 to-teal-500',
        icono: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        halo: 'bg-emerald-100/70',
    },
    amber: {
        barra: 'from-amber-400 to-orange-500',
        icono: 'bg-amber-50 text-amber-600 ring-amber-100',
        halo: 'bg-amber-100/70',
    },
    blue: {
        barra: 'from-blue-400 to-indigo-500',
        icono: 'bg-blue-50 text-blue-600 ring-blue-100',
        halo: 'bg-blue-100/70',
    },
    red: {
        barra: 'from-red-400 to-rose-500',
        icono: 'bg-red-50 text-red-600 ring-red-100',
        halo: 'bg-red-100/70',
    },
}

const tarjetas = computed(() => [
    {
        key: 'hoy',
        label: 'Ventas de hoy',
        prefijo: 'Bs.',
        valor: formatoMoneda(props.ventasHoy),
        hint: `${plural(props.ventasHoyCantidad, 'venta', 'ventas')} · ${textoVariacionHoy.value}`,
        badge: 'Hoy',
        badgeTone: props.ventasHoyCantidad > 0 ? 'green' : 'slate',
        icon: 'money',
        accent: 'emerald',
        valorClase: 'text-slate-900',
    },
    {
        key: 'mes',
        label: 'Ventas del mes',
        prefijo: 'Bs.',
        valor: formatoMoneda(props.ventasMes),
        hint: `${plural(props.ventasMesCantidad, 'venta', 'ventas')} · ticket Bs. ${formatoMoneda(props.ticketPromedio)}`,
        badge: badgeMes.value,
        badgeTone: variacionMes.value === null || variacionMes.value >= 0 ? 'green' : 'red',
        icon: 'chart',
        accent: 'blue',
        valorClase: 'text-slate-900',
    },
    {
        key: 'cobrar',
        label: 'Cuentas por cobrar',
        prefijo: 'Bs.',
        valor: formatoMoneda(props.cuentasPorCobrar),
        hint: props.ventasCreditoPendientes > 0
            ? plural(props.ventasCreditoPendientes, 'venta a crédito pendiente', 'ventas a crédito pendientes')
            : 'Sin saldos pendientes',
        badge: props.cuentasPorCobrar > 0 ? 'Pendiente' : 'Al día',
        badgeTone: props.cuentasPorCobrar > 0 ? 'amber' : 'green',
        icon: 'wallet',
        accent: 'amber',
        valorClase: props.cuentasPorCobrar > 0 ? 'text-amber-600' : 'text-slate-900',
    },
    {
        key: 'stock',
        label: 'Stock bajo',
        prefijo: null,
        valor: String(props.stockBajo),
        hint: `de ${plural(props.totalPresentaciones, 'presentación activa', 'presentaciones activas')}`,
        badge: props.stockBajo > 0 ? 'Revisar' : 'Saludable',
        badgeTone: props.stockBajo > 0 ? 'red' : 'green',
        icon: 'alert',
        accent: 'red',
        valorClase: props.stockBajo > 0 ? 'text-red-600' : 'text-slate-900',
    },
])

const totalSemana = computed(() => {
    return props.ventasUltimos7Dias.reduce((total, dia) => total + Number(dia.total ?? 0), 0)
})

const promedioSemana = computed(() => {
    const dias = props.ventasUltimos7Dias.length || 1
    return totalSemana.value / dias
})

const maxSemana = computed(() => {
    return Math.max(...props.ventasUltimos7Dias.map((dia) => Number(dia.total ?? 0)), 0)
})

const mejorDia = computed(() => {
    return props.ventasUltimos7Dias.reduce((mejor, dia) => {
        return Number(dia.total ?? 0) > Number(mejor.total ?? 0) ? dia : mejor
    }, { fecha: null, total: 0 })
})

const alturaBarra = (total) => {
    if (maxSemana.value <= 0) return 6
    return Math.max(6, Math.round((Number(total) / maxSemana.value) * 100))
}

const etiquetaDia = (fecha) => {
    const valor = fechaLocal(fecha)
    if (!valor) return ''
    return capitalizar(valor.toLocaleDateString('es-BO', { weekday: 'short' }).replace('.', ''))
}

const esHoy = (fecha) => {
    const hoy = fechaHoy()
    const year = hoy.getFullYear()
    const month = String(hoy.getMonth() + 1).padStart(2, '0')
    const day = String(hoy.getDate()).padStart(2, '0')

    return String(fecha).slice(0, 10) === `${year}-${month}-${day}`
}

const tabs = [
    {
        key: 'ventas',
        label: 'Ventas',
        titulo: 'Ventas recientes',
        subtitulo: 'Últimas operaciones registradas',
        vacio: 'No existen ventas registradas.',
        total: props.ventasRecientes.length,
    },
    {
        key: 'ingresos',
        label: 'Ingresos',
        titulo: 'Ingresos recientes',
        subtitulo: 'Últimos ingresos al almacén',
        vacio: 'No existen ingresos registrados.',
        total: props.ingresosRecientes.length,
    },
    {
        key: 'salidas',
        label: 'Salidas',
        titulo: 'Salidas recientes',
        subtitulo: 'Últimas salidas del almacén',
        vacio: 'No existen salidas registradas.',
        total: props.salidasRecientes.length,
    },
]

const tabsDisponibles = computed(() => tabs.filter((tab) => tieneAcceso(tab.key)))
const tabActivo = ref('ventas')

watch(tabsDisponibles, (disponibles) => {
    if (!disponibles.some((tab) => tab.key === tabActivo.value)) {
        tabActivo.value = disponibles[0]?.key ?? null
    }
}, { immediate: true })

const tabActual = computed(() => tabs.find((tab) => tab.key === tabActivo.value) ?? null)

const enlaceModulo = computed(() => {
    const rutas = {
        ventas: 'ventas.index',
        ingresos: 'ingresos.index',
        salidas: 'salidas.index',
    }

    return tabActivo.value ? route(rutas[tabActivo.value]) : '#'
})

const listaProductos = (detalles) => {
    if (!detalles || !detalles.length) return '-'
    return detalles
        .map((detalle) => {
            const nombre = detalle.product_variant?.product?.name ?? 'Producto'
            return Number(detalle.cantidad) > 1
                ? `${nombre} x${formatearCantidad(detalle.cantidad)}`
                : nombre
        })
        .join(', ')
}

const presentacionProducto = (variante) => presentacionVariante(variante) || 'Sin presentación'

const stockVariante = (variante) => {
    return `${formatearCantidad(variante.stock)} ${simboloUnidad(variante)}`
}

const minimoVariante = (variante) => {
    return `${formatearCantidad(variante.minimum_stock)} ${simboloUnidad(variante)}`
}

const cantidadVendida = (producto) => {
    return `${formatearCantidad(producto.total_cantidad)} ${simboloUnidad(producto.product_variant)}`
}

const rankClass = (index) => {
    return [
        'bg-amber-100 text-amber-700 ring-amber-200',
        'bg-slate-200 text-slate-700 ring-slate-300',
        'bg-orange-100 text-orange-700 ring-orange-200',
        'bg-slate-50 text-slate-500 ring-slate-200',
        'bg-slate-50 text-slate-500 ring-slate-200',
    ][index] ?? 'bg-slate-50 text-slate-500 ring-slate-200'
}

const actualizar = () => {
    router.reload({ preserveScroll: true })
}
</script>

<template>
    <AppLayout>

        <Head title="Dashboard" />

        <div class="mb-6 overflow-hidden rounded-3xl bg-slate-900 text-white shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5">
            <div class="relative p-6 sm:p-8">
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl" />
                <div class="pointer-events-none absolute -bottom-28 -left-16 h-64 w-64 rounded-full bg-teal-400/10 blur-3xl" />
                <div class="pointer-events-none absolute right-1/3 top-0 h-px w-2/5 bg-gradient-to-r from-transparent via-emerald-300/40 to-transparent" />

                <div class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.8)]" />
                            Panel operativo
                        </div>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                            {{ saludo }}, {{ nombreUsuario }}
                        </h1>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-300">
                            Este es el pulso de CEFTE para hoy. Revisa ventas, cobros y alertas de inventario en un solo lugar.
                        </p>
                        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs text-slate-300">
                            <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-inset ring-white/10">{{ fechaLarga }}</span>
                            <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-inset ring-white/10">
                                {{ totalProductos }} productos · {{ totalPresentaciones }} presentaciones
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                        <button
                            type="button"
                            @click="actualizar"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/15 transition hover:bg-white/15"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356m0 4.992l-3.181-3.182a8.25 8.25 0 11-2.893 13.68" />
                            </svg>
                            Actualizar
                        </button>
                        <Link
                            v-if="tieneAcceso('ventas')"
                            :href="route('ventas.create')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nueva venta
                        </Link>
                        <Link
                            v-if="tieneAcceso('ingresos')"
                            :href="route('ingresos.create')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/15 transition hover:bg-white/15"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 7.5L12 3m0 0L7.5 7.5M12 3v13.5" />
                            </svg>
                            Registrar ingreso
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="tarjeta in tarjetas"
                :key="tarjeta.key"
                class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70"
            >
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r" :class="acentos[tarjeta.accent].barra" />
                <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full blur-2xl" :class="acentos[tarjeta.accent].halo" />

                <div class="relative">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1 ring-inset" :class="acentos[tarjeta.accent].icono">
                            <svg v-if="tarjeta.icon === 'money'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="tarjeta.icon === 'chart'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            <svg v-else-if="tarjeta.icon === 'wallet'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9v3" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </span>

                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset" :class="tonosBadge[tarjeta.badgeTone]">
                            {{ tarjeta.badge }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm font-medium text-slate-500">{{ tarjeta.label }}</p>
                    <p class="mt-1 flex items-baseline gap-1.5">
                        <span v-if="tarjeta.prefijo" class="text-sm font-semibold text-slate-400">{{ tarjeta.prefijo }}</span>
                        <span class="text-2xl font-extrabold tracking-tight sm:text-3xl" :class="tarjeta.valorClase">{{ tarjeta.valor }}</span>
                    </p>
                    <p class="mt-2 text-xs text-slate-400">{{ tarjeta.hint }}</p>
                </div>
            </article>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Rendimiento de ventas</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Movimiento de los últimos 7 días</p>
                    </div>
                    <Badge color="green" dot>Mes actual</Badge>
                </div>

                <div class="mt-8 flex h-52 items-end gap-2 sm:gap-4">
                    <div
                        v-for="dia in ventasUltimos7Dias"
                        :key="dia.fecha"
                        class="group flex min-w-0 flex-1 flex-col items-center justify-end gap-2"
                    >
                        <div class="relative flex h-40 w-full items-end justify-center">
                            <div class="pointer-events-none absolute -top-7 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] font-semibold text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100">
                                Bs. {{ formatoMoneda(dia.total) }}
                            </div>
                            <div
                                class="w-full max-w-[34px] rounded-t-xl transition-all duration-300"
                                :class="esHoy(dia.fecha)
                                    ? 'bg-gradient-to-t from-emerald-600 to-emerald-400 shadow-lg shadow-emerald-500/20'
                                    : 'bg-gradient-to-t from-slate-200 to-slate-100 group-hover:from-emerald-200 group-hover:to-emerald-100'"
                                :style="{ height: `${alturaBarra(dia.total)}%` }"
                                :title="`${etiquetaDia(dia.fecha)}: Bs. ${formatoMoneda(dia.total)}`"
                            />
                        </div>
                        <span
                            class="text-[10px] font-semibold uppercase tracking-wide"
                            :class="esHoy(dia.fecha) ? 'text-emerald-600' : 'text-slate-400'"
                        >
                            {{ etiquetaDia(dia.fecha) }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4">
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Total 7 días</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">Bs. {{ formatoMoneda(totalSemana) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Promedio diario</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">Bs. {{ formatoMoneda(promedioSemana) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Mejor día</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">
                            <template v-if="mejorDia.total > 0">{{ etiquetaDia(mejorDia.fecha) }} · Bs. {{ formatoMoneda(mejorDia.total) }}</template>
                            <template v-else>Sin ventas</template>
                        </p>
                    </div>
                </div>
            </section>

            <aside class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 ring-1 ring-inset ring-red-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022 23.848 23.848 0 005.455 1.31M14.857 17.082a24.135 24.135 0 01-5.714 0M14.857 17.082a3 3 0 11-5.714 0" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Alertas de inventario</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Requieren seguimiento</p>
                        </div>
                    </div>
                    <Badge :color="stockBajo > 0 ? 'red' : 'green'">{{ stockBajo }}</Badge>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Stock bajo</h3>
                        <span class="text-[11px] font-medium text-slate-400">Mínimo permitido</span>
                    </div>

                    <div v-if="stockCritico.length" class="mt-2 divide-y divide-slate-100">
                        <div v-for="variante in stockCritico" :key="variante.id" class="flex items-center gap-3 py-2.5">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-red-500" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700">{{ variante.product?.name ?? 'Producto' }}</p>
                                <p class="truncate text-[11px] text-slate-400">{{ presentacionProducto(variante) }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-red-600">{{ stockVariante(variante) }}</p>
                                <p class="text-[10px] text-slate-400">Mín. {{ minimoVariante(variante) }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="mt-3 flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-3 text-sm text-emerald-700 ring-1 ring-inset ring-emerald-100">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Todo el stock está en niveles saludables.
                    </div>
                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Vencimientos</h3>
                        <Badge :color="porVencerTotal > 0 ? 'amber' : 'green'">{{ porVencerTotal }}</Badge>
                    </div>

                    <div v-if="porVencer.length" class="mt-2 divide-y divide-slate-100">
                        <div v-for="lote in porVencer" :key="lote.id" class="flex items-center gap-3 py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700">{{ lote.product_variant?.product?.name ?? 'Producto' }}</p>
                                <p class="truncate text-[11px] text-slate-400">Lote {{ lote.codigo ?? lote.id }} · {{ formatoFechaCorta(lote.fecha_caducidad) }}</p>
                            </div>
                            <Badge :color="tonoVencimiento(lote.fecha_caducidad)" class="shrink-0">
                                {{ etiquetaVencimiento(lote.fecha_caducidad) }}
                            </Badge>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-sm text-slate-400">No hay lotes próximos a vencer.</p>
                </div>

                <Link
                    v-if="tieneAcceso('variantes')"
                    :href="route('variantes.index')"
                    class="mt-5 inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                >
                    Revisar inventario
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
            </aside>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm xl:col-span-2">
                <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">{{ tabActual?.titulo ?? 'Actividad reciente' }}</h2>
                        <p class="mt-0.5 text-xs text-slate-400">{{ tabActual?.subtitulo ?? 'Últimos movimientos registrados' }}</p>
                    </div>
                    <Link
                        v-if="tabActual && tieneAcceso(tabActivo)"
                        :href="enlaceModulo"
                        class="inline-flex w-fit items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                    >
                        Ver todo
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                </div>

                <div class="flex gap-1 overflow-x-auto border-b border-slate-100 px-5 pt-3">
                    <button
                        v-for="tab in tabsDisponibles"
                        :key="tab.key"
                        type="button"
                        @click="tabActivo = tab.key"
                        class="flex items-center gap-2 whitespace-nowrap rounded-t-lg border-b-2 px-3 py-2.5 text-sm font-semibold transition-colors"
                        :class="tabActivo === tab.key
                            ? 'border-emerald-600 text-emerald-700'
                            : 'border-transparent text-slate-400 hover:text-slate-700'"
                    >
                        {{ tab.label }}
                        <span
                            class="rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                            :class="tabActivo === tab.key ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ tab.total }}
                        </span>
                    </button>
                </div>

                <div v-if="tabActivo === 'ventas'">
                    <div v-if="ventasRecientes.length" class="overflow-x-auto">
                        <table class="table-responsive w-full table-auto text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nro.</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cliente</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Productos</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="venta in ventasRecientes" :key="venta.id" class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/70">
                                    <td data-label="Nro." class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ venta.numero }}</td>
                                    <td data-label="Fecha" class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatoFecha(venta.fecha) }}</td>
                                    <td data-label="Cliente" class="whitespace-nowrap px-4 py-3 text-slate-600">{{ venta.clienteRelacion?.nombre ?? venta.cliente ?? '-' }}</td>
                                    <td data-label="Productos" class="max-w-[200px] truncate px-4 py-3 text-slate-400" :title="listaProductos(venta.detalles)">{{ listaProductos(venta.detalles) }}</td>
                                    <td data-label="Total" class="whitespace-nowrap px-4 py-3 font-bold text-slate-800">Bs. {{ formatoMoneda(venta.total) }}</td>
                                    <td data-label="Pago" class="whitespace-nowrap px-4 py-3">
                                        <Badge :color="venta.pago_tipo === 'credito' ? 'amber' : 'green'" class="text-[10px]">
                                            {{ venta.pago_tipo === 'credito' ? 'Crédito' : 'Contado' }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center px-6 py-14 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 ring-1 ring-inset ring-slate-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ tabActual?.vacio }}</p>
                    </div>
                </div>

                <div v-if="tabActivo === 'ingresos'">
                    <div v-if="ingresosRecientes.length" class="overflow-x-auto">
                        <table class="table-responsive w-full table-auto text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nro.</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Productos</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Registrado por</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ingreso in ingresosRecientes" :key="ingreso.id" class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/70">
                                    <td data-label="Nro." class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ ingreso.numero }}</td>
                                    <td data-label="Fecha" class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatoFecha(ingreso.fecha) }}</td>
                                    <td data-label="Productos" class="max-w-[200px] truncate px-4 py-3 text-slate-400" :title="listaProductos(ingreso.detalles)">{{ listaProductos(ingreso.detalles) }}</td>
                                    <td data-label="Registrado por" class="whitespace-nowrap px-4 py-3 text-slate-600">{{ ingreso.usuario?.name ?? '-' }}</td>
                                    <td data-label="Observaciones" class="px-4 py-3 text-slate-400">{{ ingreso.observaciones || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center px-6 py-14 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 ring-1 ring-inset ring-slate-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ tabActual?.vacio }}</p>
                    </div>
                </div>

                <div v-if="tabActivo === 'salidas'">
                    <div v-if="salidasRecientes.length" class="overflow-x-auto">
                        <table class="table-responsive w-full table-auto text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nro.</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Motivo</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Productos</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-slate-400">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="salida in salidasRecientes" :key="salida.id" class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/70">
                                    <td data-label="Nro." class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ salida.numero }}</td>
                                    <td data-label="Fecha" class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatoFecha(salida.fecha) }}</td>
                                    <td data-label="Motivo" class="whitespace-nowrap px-4 py-3">
                                        <Badge :color="salida.motivo === 'dano' ? 'red' : salida.motivo === 'vencimiento' ? 'orange' : 'gray'" class="text-[10px]">
                                            {{ salida.motivo === 'dano' ? 'Daño' : salida.motivo === 'vencimiento' ? 'Vencimiento' : salida.motivo === 'degusto' ? 'Degusto' : salida.motivo === 'donacion' ? 'Donación' : salida.motivo }}
                                        </Badge>
                                    </td>
                                    <td data-label="Productos" class="max-w-[200px] truncate px-4 py-3 text-slate-400" :title="listaProductos(salida.detalles)">{{ listaProductos(salida.detalles) }}</td>
                                    <td data-label="Registrado por" class="whitespace-nowrap px-4 py-3 text-slate-600">{{ salida.usuario?.name ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center px-6 py-14 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 ring-1 ring-inset ring-slate-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-medium text-slate-500">{{ tabActual?.vacio }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Más vendidos</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Top del mes actual</p>
                    </div>
                    <Badge color="indigo">Top 5</Badge>
                </div>

                <ol v-if="topProductos.length" class="mt-5 space-y-3">
                    <li v-for="(producto, index) in topProductos" :key="producto.product_variant_id" class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-extrabold ring-1 ring-inset" :class="rankClass(index)">
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-700">{{ producto.product_variant?.product?.name ?? 'Producto' }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ presentacionProducto(producto.product_variant) }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold text-slate-800">{{ cantidadVendida(producto) }}</p>
                            <p class="text-[10px] text-slate-400">Bs. {{ formatoMoneda(producto.total_monto) }}</p>
                        </div>
                    </li>
                </ol>

                <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 ring-1 ring-inset ring-slate-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </span>
                    <p class="mt-3 text-sm font-medium text-slate-500">Aún no hay ventas este mes.</p>
                </div>
            </section>
        </div>

    </AppLayout>
</template>
