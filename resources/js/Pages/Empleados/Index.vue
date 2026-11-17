<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatCard from '@/Components/UI/StatCard.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'

const props = defineProps({
    empleados: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, activos: 0, conUsuario: 0 }),
    },
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('empleados.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})

const inactivos = computed(() => Math.max(0, (props.resumen.total ?? 0) - (props.resumen.activos ?? 0)))

const headers = [
    'Empleado',
    'Contacto',
    'Profesión',
    'Usuario',
    'Estado',
    'Acciones'
]

const iniciales = (empleado) => {
    return `${empleado.first_name?.charAt(0) ?? ''}${empleado.first_surname?.charAt(0) ?? ''}`.toUpperCase()
}

const nombreCompleto = (empleado) => {
    return [empleado.first_name, empleado.first_surname, empleado.second_surname]
        .filter(Boolean)
        .join(' ')
}

const toggleActivo = (id, is_active) => {
    if (is_active) {
        if (!confirm('¿Está seguro de desactivar este empleado?')) {
            return
        }
        router.delete(
            route('empleados.destroy', id),
            { preserveScroll: true }
        )
    } else {
        if (!confirm('¿Está seguro de activar este empleado?')) {
            return
        }
        router.post(
            route('empleados.activar', id),
            {},
            { preserveScroll: true }
        )
    }
}

</script>

<template>

    <Head title="Empleados" />

    <AppLayout>

        <PageHeader
            title="Empleados"
            subtitle="Gestión del personal registrado en el sistema"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por nombre, CI, teléfono..."
                />

                <Link :href="route('empleados.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo empleado
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total empleados" :value="resumen.total" hint="Personal registrado" color="indigo">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Activos" :value="resumen.activos" hint="Disponibles actualmente" color="emerald">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Con usuario" :value="resumen.conUsuario" hint="Acceso al sistema" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Inactivos" :value="inactivos" hint="Dados de baja" color="slate">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de empleados</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ empleados.total }} registro{{ empleados.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="empleado in empleados.data"
                :key="empleado.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-50': !empleado.is_active }"
            >

                <td data-label="Empleado" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ iniciales(empleado) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ nombreCompleto(empleado) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                CI {{ empleado.identity_card || '—' }}
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Contacto" class="px-5 py-4">
                    <p class="text-slate-600">{{ empleado.phone || '—' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ empleado.email || 'Sin correo' }}</p>
                </td>

                <td data-label="Profesión" class="px-5 py-4 text-slate-600">
                    {{ empleado.profession || '—' }}
                </td>

                <td data-label="Usuario" class="px-5 py-4">
                    <Badge v-if="empleado.user" color="blue" dot>
                        {{ empleado.user.roles?.length ? empleado.user.roles[0].name : 'Sin rol' }}
                    </Badge>
                    <Badge v-else color="gray" dot>
                        Sin usuario
                    </Badge>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="empleado.is_active ? 'green' : 'red'" dot>
                        {{ empleado.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            v-if="empleado.is_active"
                            :href="route('empleados.edit', empleado.id)"
                            title="Editar empleado"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <Link
                            v-if="empleado.is_active && !empleado.user"
                            :href="route('empleados.usuario', empleado.id)"
                            title="Asignar usuario"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                            </svg>
                        </Link>

                        <button
                            type="button"
                            :title="empleado.is_active ? 'Desactivar empleado' : 'Activar empleado'"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition"
                            :class="empleado.is_active
                                ? 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                : 'text-emerald-600 bg-emerald-50 border-emerald-200 hover:bg-emerald-100'"
                            @click="toggleActivo(empleado.id, empleado.is_active)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="empleados.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron empleados"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Aún no hay personal registrado. Crea el primer empleado para comenzar.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                            <Link v-else :href="route('empleados.create')">
                                <Button>Registrar empleado</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="empleados.links" />

    </AppLayout>

</template>
