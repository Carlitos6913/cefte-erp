<script setup>
import { computed, reactive, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import ResumenSeccion from './Partials/ResumenSeccion.vue'
import VentasSeccion from './Partials/VentasSeccion.vue'
import InventarioSeccion from './Partials/InventarioSeccion.vue'
import IngresosSeccion from './Partials/IngresosSeccion.vue'
import CobranzasSeccion from './Partials/CobranzasSeccion.vue'
import { fechaLocal, formatearFechaCorta } from '@/utils/informes'

const props = defineProps({
    resumen: Object,
    comparativo: Object,
    ventas: Object,
    inventario: Object,
    ingresos: Object,
    cobranzas: Object,
    tendencia: Object,
    detalle: Object,
    periodo: Object,
    filtros: Object,
    seccion: {
        type: String,
        default: 'resumen',
    },
    esAdministrador: Boolean,
})

const form = reactive({
    fecha_inicio: props.filtros?.fecha_inicio ?? '',
    fecha_fin: props.filtros?.fecha_fin ?? '',
})

watch(
    () => props.filtros,
    (filtros) => {
        form.fecha_inicio = filtros?.fecha_inicio ?? ''
        form.fecha_fin = filtros?.fecha_fin ?? ''
    }
)

const secciones = [
    {
        clave: 'resumen',
        etiqueta: 'Resumen ejecutivo',
        icono: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    },
    {
        clave: 'ventas',
        etiqueta: 'Ventas',
        icono: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        clave: 'inventario',
        etiqueta: 'Inventario',
        icono: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    },
    {
        clave: 'ingresos',
        etiqueta: 'Compras e ingresos',
        icono: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
    },
    {
        clave: 'cobranzas',
        etiqueta: 'Cobranzas',
        icono: 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6.75A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V12m18 0V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75V12',
    },
]

const fechaISO = (fecha) => {
    const year = fecha.getFullYear()
    const month = String(fecha.getMonth() + 1).padStart(2, '0')
    const day = String(fecha.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const atajos = [
    {
        etiqueta: 'Hoy',
        rango: () => {
            const hoy = new Date()
            return [hoy, hoy]
        },
    },
    {
        etiqueta: 'Últimos 7 días',
        rango: () => {
            const fin = new Date()
            const inicio = new Date()
            inicio.setDate(fin.getDate() - 6)
            return [inicio, fin]
        },
    },
    {
        etiqueta: 'Últimos 30 días',
        rango: () => {
            const fin = new Date()
            const inicio = new Date()
            inicio.setDate(fin.getDate() - 29)
            return [inicio, fin]
        },
    },
    {
        etiqueta: 'Este mes',
        rango: () => {
            const hoy = new Date()
            const inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
            return [inicio, hoy]
        },
    },
    {
        etiqueta: 'Mes anterior',
        rango: () => {
            const hoy = new Date()
            const inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1)
            const fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0)
            return [inicio, fin]
        },
    },
    {
        etiqueta: 'Este año',
        rango: () => {
            const hoy = new Date()
            const inicio = new Date(hoy.getFullYear(), 0, 1)
            return [inicio, hoy]
        },
    },
]

const consulta = (extra = {}) => ({
    fecha_inicio: form.fecha_inicio,
    fecha_fin: form.fecha_fin,
    seccion: extra.seccion ?? props.seccion,
})

const navegar = (extra = {}) => {
    router.get(route('informes.index'), consulta(extra), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const filtrar = () => navegar()

const cambiarSeccion = (clave) => {
    if (clave === props.seccion) return

    navegar({ seccion: clave })
}

const aplicarAtajo = (atajo) => {
    const [inicio, fin] = atajo.rango()
    form.fecha_inicio = fechaISO(inicio)
    form.fecha_fin = fechaISO(fin)
    navegar()
}

const restablecer = () => {
    const hoy = new Date()
    const inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
    form.fecha_inicio = fechaISO(inicio)
    form.fecha_fin = fechaISO(hoy)
    navegar()
}

const atajoActivo = (atajo) => {
    const [inicio, fin] = atajo.rango()

    return form.fecha_inicio === fechaISO(inicio) && form.fecha_fin === fechaISO(fin)
}

const rangoTexto = computed(() => {
    const inicio = fechaLocal(props.periodo?.inicio)
    const fin = fechaLocal(props.periodo?.fin)

    if (!inicio || !fin) return ''

    return `${formatearFechaCorta(props.periodo.inicio)} — ${formatearFechaCorta(props.periodo.fin)}`
})

const comparativoTexto = computed(() => {
    const inicio = fechaLocal(props.periodo?.inicio_anterior)
    const fin = fechaLocal(props.periodo?.fin_anterior)

    if (!inicio || !fin) return ''

    return `Comparado con ${formatearFechaCorta(props.periodo.inicio_anterior)} — ${formatearFechaCorta(props.periodo.fin_anterior)}`
})

const exportarUrl = computed(() => route('informes.exportar', {
    fecha_inicio: form.fecha_inicio,
    fecha_fin: form.fecha_fin,
    seccion: props.seccion,
}))
</script>

<template>
    <Head title="Informes" />

    <AppLayout>
        <PageHeader
            title="Centro de informes"
            subtitle="Analiza ventas, inventario, compras y cobranzas del periodo seleccionado."
        >
            <template #icon>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </template>

            <template #actions>
                <a
                    :href="exportarUrl"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Exportar CSV
                </a>
            </template>
        </PageHeader>

        <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-1 overflow-x-auto pb-1 lg:pb-0">
                    <button
                        v-for="tab in secciones"
                        :key="tab.clave"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition"
                        :class="seccion === tab.clave
                            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                        :aria-current="seccion === tab.clave ? 'page' : undefined"
                        @click="cambiarSeccion(tab.clave)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icono" />
                        </svg>
                        {{ tab.etiqueta }}
                    </button>
                </div>

                <div class="hidden shrink-0 text-right lg:block">
                    <p class="text-xs font-semibold text-slate-600">{{ rangoTexto }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">{{ comparativoTexto }}</p>
                </div>
            </div>

            <form class="p-4" @submit.prevent="filtrar">
                <div class="mb-4 flex flex-wrap gap-1.5">
                    <button
                        v-for="atajo in atajos"
                        :key="atajo.etiqueta"
                        type="button"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold transition"
                        :class="atajoActivo(atajo)
                            ? 'bg-slate-800 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700'"
                        @click="aplicarAtajo(atajo)"
                    >
                        {{ atajo.etiqueta }}
                    </button>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                    <div class="w-full sm:w-auto">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Fecha inicio
                        </label>
                        <input
                            v-model="form.fecha_inicio"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-emerald-500 sm:w-44"
                        />
                    </div>

                    <div class="w-full sm:w-auto">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Fecha fin
                        </label>
                        <input
                            v-model="form.fecha_fin"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-emerald-500 sm:w-44"
                        />
                    </div>

                    <Button type="submit">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        Aplicar periodo
                    </Button>

                    <Button color="secondary" @click="restablecer">
                        Mes actual
                    </Button>

                    <div class="text-xs text-slate-400 sm:ml-auto sm:text-right">
                        <p class="font-semibold text-slate-600">{{ rangoTexto }}</p>
                        <p class="mt-0.5">{{ periodo?.dias }} días · {{ comparativoTexto }}</p>
                    </div>
                </div>
            </form>
        </div>

        <ResumenSeccion
            v-if="seccion === 'resumen'"
            :resumen="resumen"
            :comparativo="comparativo"
            :tendencia="tendencia"
            :ventas="ventas"
            :inventario="inventario"
            :cobranzas="cobranzas"
        />

        <VentasSeccion
            v-else-if="seccion === 'ventas'"
            :resumen="resumen"
            :comparativo="comparativo"
            :tendencia="tendencia"
            :ventas="ventas"
            :detalle="detalle"
            :es-administrador="esAdministrador"
        />

        <InventarioSeccion
            v-else-if="seccion === 'inventario'"
            :resumen="resumen"
            :inventario="inventario"
        />

        <IngresosSeccion
            v-else-if="seccion === 'ingresos'"
            :resumen="resumen"
            :comparativo="comparativo"
            :tendencia="tendencia"
            :ingresos="ingresos"
            :detalle="detalle"
            :es-administrador="esAdministrador"
        />

        <CobranzasSeccion
            v-else-if="seccion === 'cobranzas'"
            :resumen="resumen"
            :comparativo="comparativo"
            :tendencia="tendencia"
            :cobranzas="cobranzas"
            :detalle="detalle"
            :es-administrador="esAdministrador"
        />
    </AppLayout>
</template>
