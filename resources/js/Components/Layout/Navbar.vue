<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useSidebar } from '@/stores/useSidebar'

const { collapsed, toggle } = useSidebar()

const page = usePage()

const mapaRutas = {
    'Dashboard': { titulo: 'Dashboard', subtitulo: 'Resumen general del sistema' },
    'Profile/Edit': { titulo: 'Mi perfil', subtitulo: 'Gestiona tu información personal' },
    'Administracion/Usuarios/Index': { titulo: 'Usuarios', subtitulo: 'Gestión de usuarios del sistema' },
    'Administracion/Usuarios/Create': { titulo: 'Nuevo usuario', subtitulo: 'Crear un nuevo usuario' },
    'Administracion/Usuarios/Edit': { titulo: 'Editar usuario', subtitulo: 'Modificar datos del usuario' },
    'Empleados/Index': { titulo: 'Empleados', subtitulo: 'Gestión del personal registrado' },
    'Empleados/Create': { titulo: 'Nuevo empleado', subtitulo: 'Registrar un nuevo empleado' },
    'Empleados/Edit': { titulo: 'Editar empleado', subtitulo: 'Modificar datos del empleado' },
    'Empleados/Usuario': { titulo: 'Asignar usuario', subtitulo: 'Crear usuario y otorgar rol al empleado' },
    'Clientes/Index': { titulo: 'Clientes', subtitulo: 'Gestión de clientes' },
    'Clientes/Create': { titulo: 'Nuevo cliente', subtitulo: 'Registrar un nuevo cliente' },
    'Clientes/Edit': { titulo: 'Editar cliente', subtitulo: 'Modificar datos del cliente' },
    'Inventario/Categorias/Index': { titulo: 'Categorías', subtitulo: 'Gestión de categorías de productos' },
    'Inventario/Categorias/Create': { titulo: 'Nueva categoría', subtitulo: 'Crear una nueva categoría' },
    'Inventario/Categorias/Edit': { titulo: 'Editar categoría', subtitulo: 'Modificar la categoría' },
    'Inventario/UnidadesMedida/Index': { titulo: 'Unidades de medida', subtitulo: 'Gestión de unidades de medida' },
    'Inventario/UnidadesMedida/Create': { titulo: 'Nueva unidad de medida', subtitulo: 'Crear una nueva unidad' },
    'Inventario/UnidadesMedida/Edit': { titulo: 'Editar unidad de medida', subtitulo: 'Modificar la unidad de medida' },
    'Inventario/Productos/Index': { titulo: 'Productos', subtitulo: 'Gestión de productos en inventario' },
    'Inventario/Productos/Create': { titulo: 'Nuevo producto', subtitulo: 'Registrar un nuevo producto' },
    'Inventario/Productos/Edit': { titulo: 'Editar producto', subtitulo: 'Modificar datos del producto' },
    'Inventario/Variantes/Index': { titulo: 'Variantes', subtitulo: 'Gestión de variantes de producto' },
    'Inventario/Variantes/Create': { titulo: 'Nueva variante', subtitulo: 'Registrar una nueva variante' },
    'Inventario/Variantes/Edit': { titulo: 'Editar variante', subtitulo: 'Modificar la variante' },
    'Inventario/Ingresos/Index': { titulo: 'Ingresos', subtitulo: 'Registro de ingresos al almacén' },
    'Inventario/Ingresos/Create': { titulo: 'Nuevo ingreso', subtitulo: 'Registrar un nuevo ingreso' },
    'Inventario/Ingresos/Edit': { titulo: 'Editar ingreso', subtitulo: 'Modificar el ingreso' },
    'Inventario/Ingresos/Show': { titulo: 'Detalle de ingreso', subtitulo: 'Ver detalle del ingreso' },
    'Inventario/Salidas/Index': { titulo: 'Salidas', subtitulo: 'Registro y control de salidas de productos' },
    'Inventario/Salidas/Create': { titulo: 'Nueva salida', subtitulo: 'Registrar una nueva salida' },
    'Inventario/Salidas/Edit': { titulo: 'Editar salida', subtitulo: 'Modificar la salida' },
    'Inventario/Salidas/Show': { titulo: 'Detalle de salida', subtitulo: 'Ver detalle de la salida' },
    'Ventas/Index': { titulo: 'Ventas', subtitulo: 'Gestión de ventas realizadas' },
    'Ventas/Create': { titulo: 'Nueva venta', subtitulo: 'Registrar una nueva venta' },
    'Ventas/Edit': { titulo: 'Editar venta', subtitulo: 'Modificar la venta' },
    'Ventas/Show': { titulo: 'Detalle de venta', subtitulo: 'Ver detalle de la venta' },
    'Pagos/Index': { titulo: 'Pagos', subtitulo: 'Gestión de pagos recibidos' },
    'Pagos/Create': { titulo: 'Nuevo pago', subtitulo: 'Registrar un nuevo pago' },
    'Pagos/Show': { titulo: 'Detalle de pago', subtitulo: 'Ver detalle del pago' },
    'Informes/Index': { titulo: 'Informes', subtitulo: 'Reportes y estadísticas' },
}

const rutaActual = computed(() => {
    const componente = page.component
    return mapaRutas[componente] ?? { titulo: 'CEFTE ERP', subtitulo: '' }
})

const usuario = computed(() => page.props.auth.user)
</script>

<template>
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0">

        <div class="flex items-center gap-3">

            <!-- Ocultar / expandir menú -->
            <button
                type="button"
                @click="toggle"
                :title="collapsed ? 'Expandir menú' : 'Ocultar menú'"
                class="flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path
                        v-if="!collapsed"
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"
                    />
                    <path
                        v-else
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5"
                    />
                </svg>
            </button>

            <div>
                <h1 class="text-lg font-bold text-slate-800">
                    {{ rutaActual.titulo }}
                </h1>
                <p class="text-xs text-slate-400">
                    {{ rutaActual.subtitulo }}
                </p>
            </div>

        </div>

        <div class="flex items-center gap-4">

            <!-- Fecha actual -->
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>{{ new Date().toLocaleDateString('es-BO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
            </div>

            <!-- Usuario - clickable -->
            <Link
                href="/profile"
                class="flex items-center gap-3 pl-4 border-l border-slate-200 hover:bg-slate-50 rounded-xl pr-3 py-1.5 -mr-3 transition"
            >
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ usuario.name.charAt(0).toUpperCase() }}
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-slate-700 leading-tight">
                        {{ usuario.name }}
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ usuario.roles?.[0]?.name ?? 'Sin rol' }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </Link>

        </div>

    </header>
</template>
