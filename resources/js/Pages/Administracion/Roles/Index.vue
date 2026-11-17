<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    roles: Array,
    modulosDisponibles: Object,
})

const totalUsuarios = computed(() => {
    return (props.roles ?? []).reduce((total, rol) => total + (rol.users_count ?? 0), 0)
})

const totalModulos = computed(() => Object.keys(props.modulosDisponibles ?? {}).length)

const iconoRol = (rol) => {
    if (rol.name === 'Administrador') return 'shield'
    if (rol.name === 'Almacenero') return 'archive'
    if (rol.name === 'Vendedor') return 'cart'
    return 'users'
}

const colorRol = (rol) => {
    if (rol.name === 'Administrador') return { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-100' }
    if (rol.name === 'Almacenero') return { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-100' }
    if (rol.name === 'Vendedor') return { bg: 'bg-amber-50', text: 'text-amber-600', border: 'border-amber-100' }
    return { bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200' }
}

const eliminar = (rol) => {
    if (!confirm(`¿Está seguro de eliminar el rol "${rol.name}"?`)) {
        return
    }

    router.delete(route('roles.destroy', rol.id), { preserveScroll: true })
}

</script>

<template>

    <Head title="Roles" />

    <AppLayout>

        <PageHeader
            title="Roles y permisos"
            subtitle="Define qué módulos puede ver cada rol del sistema"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </template>

            <template #actions>
                <Link
                    :href="route('usuarios.index')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Volver a usuarios
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <StatCard label="Roles registrados" :value="roles.length" hint="Perfiles de acceso" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Usuarios asignados" :value="totalUsuarios" hint="Cuentas con rol" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Módulos del sistema" :value="totalModulos" hint="Áreas configurables" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <div
                v-for="rol in roles"
                :key="rol.id"
                class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-start justify-between gap-3">

                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-11 h-11 rounded-xl border flex items-center justify-center shrink-0"
                                :class="[colorRol(rol).bg, colorRol(rol).text, colorRol(rol).border]"
                            >
                                <svg v-if="iconoRol(rol) === 'shield'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <svg v-else-if="iconoRol(rol) === 'archive'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <svg v-else-if="iconoRol(rol) === 'cart'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-bold text-slate-800 truncate">{{ rol.name }}</h2>
                                    <Badge v-if="rol.name === 'Administrador'" color="emerald" dot>Protegido</Badge>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ rol.users_count }} usuario{{ rol.users_count === 1 ? '' : 's' }} ·
                                    {{ rol.permissions?.length ?? 0 }} permiso{{ (rol.permissions?.length ?? 0) === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <Link
                                :href="route('roles.edit', rol.id)"
                                title="Editar rol"
                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                            </Link>

                            <button
                                v-if="rol.name !== 'Administrador'"
                                type="button"
                                title="Eliminar rol"
                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                                @click="eliminar(rol)"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>

                <div class="p-5">

                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Acceso a módulos
                    </p>

                    <div v-if="rol.accesoTotal" class="flex items-start gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-emerald-700">Acceso total</p>
                            <p class="text-xs text-emerald-600/80 mt-0.5">Puede acceder a todos los módulos del sistema.</p>
                        </div>
                    </div>

                    <div v-else class="flex flex-wrap gap-1.5">
                        <span
                            v-for="modulo in rol.modulos"
                            :key="modulo"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600"
                        >
                            {{ modulosDisponibles[modulo] ?? modulo }}
                        </span>
                        <span v-if="!rol.modulos?.length" class="text-xs text-slate-400">
                            Sin módulos asignados.
                        </span>
                    </div>

                </div>
            </div>

        </div>

    </AppLayout>

</template>
