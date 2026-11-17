<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

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
    clientes: Object,
    resumen: {
        type: Object,
        default: () => ({ total: 0, activos: 0, inactivos: 0, conEmail: 0 }),
    },
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('clientes.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})

const headers = [
    'Cliente',
    'Contacto',
    'Estado',
    'Acciones'
]

const iniciales = (cliente) => {
    return (cliente.nombre ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte.charAt(0))
        .join('')
        .toUpperCase()
}


const toggleActivo = (id, activo) => {
    if (activo) {
        if (!confirm('¿Está seguro de desactivar este cliente?')) {
            return
        }
        router.delete(
            route('clientes.destroy', id),
            { preserveScroll: true }
        )
    } else {
        if (!confirm('¿Está seguro de activar este cliente?')) {
            return
        }
        router.post(
            route('clientes.activar', id),
            {},
            { preserveScroll: true }
        )
    }
}

</script>


<template>

    <Head title="Clientes" />

    <AppLayout>

        <PageHeader
            title="Clientes"
            subtitle="Gestión de clientes registrados en el sistema"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </template>

            <template #actions>
                <SearchInput
                    v-model="buscar"
                    placeholder="Buscar por nombre o CI/NIT..."
                />

                <Link :href="route('clientes.create')">
                    <Button>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo cliente
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard label="Total clientes" :value="resumen.total" hint="Registrados en el sistema" color="indigo">
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

            <StatCard label="Con correo" :value="resumen.conEmail" hint="Contacto por email" color="blue">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </template>
            </StatCard>

            <StatCard label="Inactivos" :value="resumen.inactivos" hint="Dados de baja" color="red">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </template>
            </StatCard>
        </div>

        <div class="flex items-center justify-between gap-3 mb-3 px-1">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Listado de clientes</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ clientes.total }} registro{{ clientes.total === 1 ? '' : 's' }}
                    <span v-if="filters.buscar"> · filtrados por “{{ filters.buscar }}”</span>
                </p>
            </div>
        </div>

        <Table :headers="headers">

            <tr
                v-for="cliente in clientes.data"
                :key="cliente.id"
                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                :class="{ 'opacity-50': !cliente.activo }"
            >

                <td data-label="Cliente" class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ iniciales(cliente) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 truncate">
                                {{ cliente.nombre }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                CI/NIT {{ cliente.ci_nit || '—' }}
                            </p>
                        </div>
                    </div>
                </td>

                <td data-label="Contacto" class="px-5 py-4">
                    <p class="text-slate-600">{{ cliente.telefono || '—' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ cliente.email || 'Sin correo' }}</p>
                </td>

                <td data-label="Estado" class="px-5 py-4">
                    <Badge :color="cliente.activo ? 'green' : 'red'" dot>
                        {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="px-5 py-4 table-actions">
                    <div class="flex items-center justify-end gap-1.5">

                        <Link
                            v-if="cliente.activo"
                            :href="route('clientes.edit', cliente.id)"
                            title="Editar cliente"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </Link>

                        <button
                            type="button"
                            :title="cliente.activo ? 'Desactivar cliente' : 'Activar cliente'"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition"
                            :class="cliente.activo
                                ? 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200'
                                : 'text-emerald-600 bg-emerald-50 border-emerald-200 hover:bg-emerald-100'"
                            @click="toggleActivo(cliente.id, cliente.activo)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                        </button>

                    </div>
                </td>

            </tr>

            <tr v-if="clientes.data.length === 0" class="table-empty">
                <td :colspan="headers.length">
                    <EmptyState
                        title="No se encontraron clientes"
                        :message="filters.buscar
                            ? 'Prueba con otro término de búsqueda o limpia el filtro actual.'
                            : 'Aún no hay clientes registrados. Crea el primer cliente para comenzar.'"
                    >
                        <template #action>
                            <Button v-if="filters.buscar" color="secondary" @click="buscar = ''">
                                Limpiar búsqueda
                            </Button>
                            <Link v-else :href="route('clientes.create')">
                                <Button>Registrar cliente</Button>
                            </Link>
                        </template>
                    </EmptyState>
                </td>
            </tr>

        </Table>

        <Pagination :links="clientes.links" />

    </AppLayout>

</template>
