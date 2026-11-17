<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'

import { ref, watch } from 'vue'


const props = defineProps({
    usuarios: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, activos: 0, inactivos: 0, conRol: 0 }),
    },
    filters: Object
})


const buscar = ref(props.filters.buscar ?? '')


watch(buscar, (value) => {

    router.get(
        route('usuarios.index'),
        {
            buscar: value
        },
        {
            preserveState: true,
            replace: true
        }
    )

})


const headers = [
    'Usuario',
    'Rol',
    'Estado',
    'Acciones'
]


const iniciales = (usuario) => {
    return (usuario.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte.charAt(0))
        .join('')
        .toUpperCase()
}


const colorRol = (rol) => {
    switch (rol) {
        case 'Administrador': return 'indigo'
        case 'Almacenero': return 'blue'
        case 'Vendedor': return 'green'
        default: return 'gray'
    }
}


const toggleActivo = (id, activo) => {
    if (activo) {
        if (!confirm('¿Está seguro de desactivar este usuario?')) {
            return
        }
        router.delete(
            route('usuarios.destroy', id),
            { preserveScroll: true }
        )
    } else {
        if (!confirm('¿Está seguro de activar este usuario?')) {
            return
        }
        router.post(
            route('usuarios.activar', id),
            {},
            { preserveScroll: true }
        )
    }
}

</script>


<template>

    <Head title="Usuarios" />

    <AppLayout>

        <PageHeader
            title="Usuarios"
            subtitle="Gestión de usuarios y accesos al sistema"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por nombre, CI, correo..."
                />

                <Link
                    :href="route('roles.index')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Roles y permisos
                </Link>

                <Link :href="route('usuarios.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo usuario
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total usuarios" :value="resumen.total" hint="Cuentas registradas" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Activos" :value="resumen.activos" hint="Acceso habilitado" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Con rol" :value="resumen.conRol" hint="Con permisos asignados" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Inactivos" :value="resumen.inactivos" hint="Acceso deshabilitado" color="red">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de usuarios</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ usuarios.total }} registro{{ usuarios.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="usuario in usuarios.data"
                :key="usuario.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-50': !usuario.is_active }"
            >

                <td data-label="Usuario" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ iniciales(usuario) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ usuario.name }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[220px]">
                                {{ usuario.email }}
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Rol" class="px-5 py-4">
                    <Badge :color="colorRol(usuario.roles.length ? usuario.roles[0].name : '')" dot>
                        {{
                            usuario.roles.length
                                ? usuario.roles[0].name
                                : 'Sin rol'
                        }}
                    </Badge>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="usuario.is_active ? 'green' : 'red'" dot>
                        {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            v-if="usuario.is_active"
                            :href="route('usuarios.edit', usuario.id)"
                            title="Editar usuario"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            type="button"
                            :title="usuario.is_active ? 'Desactivar usuario' : 'Activar usuario'"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition"
                            :class="usuario.is_active
                                ? 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                : 'text-emerald-600 bg-emerald-50 border-emerald-200 hover:bg-emerald-100'"
                            @click="toggleActivo(usuario.id, usuario.is_active)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="usuarios.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron usuarios"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Aún no hay usuarios registrados. Crea el primer usuario para comenzar.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                            <Link v-else :href="route('usuarios.create')">
                                <Button>Registrar usuario</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="usuarios.links" />

    </AppLayout>

</template>
