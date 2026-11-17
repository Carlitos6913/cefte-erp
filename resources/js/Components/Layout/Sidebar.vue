<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { useSidebar } from '@/stores/useSidebar'

const page = usePage()

const { collapsed, mobileOpen, closeMobile } = useSidebar()

watch(() => page.component, () => closeMobile())

const usuario = page.props.auth.user

const modulosUsuario = computed(() => {
    const rol = usuario?.roles?.[0]
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

const rutaActual = computed(() => page.component)

const esRuta = (component) => rutaActual.value === component

const nombreModulo = (modulo) => modulo.charAt(0).toUpperCase() + modulo.slice(1)

const cerrarSesion = () => {
    closeMobile()
    router.post(route('logout'))
}

const secciones = [
    {
        titulo: 'Principal',
        items: [
            {
                modulo: 'dashboard',
                href: '/dashboard',
                componente: 'Dashboard',
                icono: 'home',
            },
        ],
    },
    {
        titulo: 'Gestión',
        items: [
            {
                modulo: 'usuarios',
                href: '/usuarios',
                componente: 'Administracion/Usuarios/Index',
                icono: 'users',
            },
            {
                modulo: 'empleados',
                href: '/empleados',
                componente: 'Empleados/Index',
                icono: 'identification',
            },
            {
                modulo: 'clientes',
                href: '/clientes',
                componente: 'Clientes/Index',
                icono: 'user-group',
            },
        ],
    },
    {
        titulo: 'Inventario',
        items: [
            {
                modulo: 'categorias',
                href: '/categorias',
                componente: 'Inventario/Categorias/Index',
                icono: 'tag',
            },
            {
                modulo: 'unidades',
                href: '/unidades',
                componente: 'Inventario/UnidadesMedida/Index',
                icono: 'building-library',
            },
            {
                modulo: 'productos',
                href: '/productos',
                componente: 'Inventario/Productos/Index',
                icono: 'cube',
            },
            {
                modulo: 'variantes',
                href: '/variantes',
                componente: 'Inventario/Variantes/Index',
                icono: 'swatch',
            },
            {
                modulo: 'ingresos',
                href: '/ingresos',
                componente: 'Inventario/Ingresos/Index',
                icono: 'arrow-down-tray',
            },
            {
                modulo: 'salidas',
                href: '/salidas',
                componente: 'Inventario/Salidas/Index',
                icono: 'arrow-up-tray',
            },
        ],
    },
    {
        titulo: 'Operaciones',
        items: [
            {
                modulo: 'ventas',
                href: '/ventas',
                componente: 'Ventas/Index',
                icono: 'currency-dollar',
            },
            {
                modulo: 'pagos',
                href: '/pagos',
                componente: 'Pagos/Index',
                icono: 'banknotes',
            },
        ],
    },
    {
        titulo: 'Análisis',
        items: [
            {
                modulo: 'informes',
                href: '/informes',
                componente: 'Informes/Index',
                icono: 'chart-bar',
            },
        ],
    },
]
</script>

<template>

    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 to-slate-950 text-white flex flex-col overflow-y-auto overflow-x-hidden sidebar-scroll transition-transform duration-200 lg:static lg:h-screen lg:shrink-0 lg:transition-all"
        :class="[
            collapsed ? 'lg:w-16' : 'lg:w-64',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]"
    >

        <!-- Logo -->
        <div
            class="h-20 flex items-center border-b border-white/10 shrink-0 relative"
            :class="collapsed ? 'justify-center px-0' : 'px-6'"
        >
            <button
                type="button"
                @click="closeMobile"
                aria-label="Cerrar menú"
                class="lg:hidden absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div
                class="flex items-center"
                :class="collapsed ? 'justify-center' : 'gap-3'"
            >
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center shrink-0">
                    <span class="text-emerald-400 font-bold text-lg">C</span>
                </div>
                <div v-if="!collapsed">
                    <div class="text-lg font-bold tracking-tight">CEFTE</div>
                    <div class="text-[10px] text-emerald-400 font-semibold tracking-[0.2em] uppercase">Sistema CEFTE</div>
                </div>
            </div>
        </div>

        <!-- Menú por secciones -->
        <nav
            class="flex-1 py-4 px-3 space-y-6 whitespace-nowrap"
            :class="collapsed ? 'px-2' : 'px-3'"
        >

            <div v-for="seccion in secciones" :key="seccion.titulo">

                <p
                    v-if="!collapsed"
                    class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500"
                >
                    {{ seccion.titulo }}
                </p>

                <div
                    v-else
                    class="mb-2 mx-4 h-px bg-white/10"
                />

                <div class="space-y-1">
                    <template v-for="item in seccion.items" :key="item.href">
                        <Link
                            v-if="tieneAcceso(item.modulo)"
                            :href="item.href"
                            :title="collapsed ? nombreModulo(item.modulo) : ''"
                            class="flex items-center rounded-lg text-sm font-medium transition-all duration-150"
                            :class="[
                                collapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2.5',
                                esRuta(item.componente)
                                    ? 'bg-emerald-500/15 text-emerald-400'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-white',
                            ]"
                        >
                            <!-- home -->
                            <svg v-if="item.icono === 'home'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>

                            <!-- users -->
                            <svg v-if="item.icono === 'users'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>

                            <!-- user-group -->
                            <svg v-if="item.icono === 'user-group'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>

                            <!-- identification -->
                            <svg v-if="item.icono === 'identification'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>

                            <!-- tag -->
                            <svg v-if="item.icono === 'tag'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>

                            <!-- cube -->
                            <svg v-if="item.icono === 'cube'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                            </svg>

                            <!-- arrow-down-tray -->
                            <svg v-if="item.icono === 'arrow-down-tray'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>

                            <!-- arrow-up-tray -->
                            <svg v-if="item.icono === 'arrow-up-tray'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>

                            <!-- currency-dollar -->
                            <svg v-if="item.icono === 'currency-dollar'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                            <!-- banknotes -->
                            <svg v-if="item.icono === 'banknotes'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>

                            <!-- chart-bar -->
                            <svg v-if="item.icono === 'chart-bar'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>

                            <!-- building-library -->
                            <svg v-if="item.icono === 'building-library'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                            </svg>

                            <!-- swatch -->
                            <svg v-if="item.icono === 'swatch'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
                            </svg>

                            <span v-if="!collapsed">{{ nombreModulo(item.modulo) }}</span>

                            <div
                                v-if="esRuta(item.componente)"
                                class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"
                                :class="collapsed ? '' : 'ml-auto'"
                            />
                        </Link>
                    </template>
                </div>

            </div>

        </nav>

        <!-- Cerrar sesión -->
        <div class="p-3 border-t border-white/10 shrink-0">

            <button
                type="button"
                @click="cerrarSesion"
                :title="collapsed ? 'Cerrar sesión' : ''"
                class="w-full flex items-center rounded-lg text-sm font-medium text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition"
                :class="collapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2.5'"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                <span v-if="!collapsed">Cerrar sesión</span>
            </button>

        </div>

    </aside>

</template>

<style scoped>
.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>