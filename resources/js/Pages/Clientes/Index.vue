<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    clientes: Object,
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
    'Nombre',
    'CI / NIT',
    'Teléfono',
    'Email',
    'Estado',
    'Acciones'
]


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

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Clientes
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Gestión de clientes registrados
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar cliente..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />

                <Link :href="route('clientes.create')">
                    <Button>
                        Nuevo cliente
                    </Button>
                </Link>

            </div>

        </div>


        <Table :headers="headers">

            <tr
                v-for="cliente in clientes.data"
                :key="cliente.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
                :class="{ 'opacity-40': !cliente.activo }"
            >

                <td data-label="Nombre" class="p-4 font-medium text-slate-800">
                    {{ cliente.nombre }}
                </td>

                <td data-label="CI / NIT" class="p-4 text-slate-600">
                    {{ cliente.ci_nit || '-' }}
                </td>

                <td data-label="Teléfono" class="p-4 text-slate-600">
                    {{ cliente.telefono || '-' }}
                </td>

                <td data-label="Email" class="p-4 text-slate-600">
                    {{ cliente.email || '-' }}
                </td>

                <td data-label="Estado" class="p-4">
                    <Badge :color="cliente.activo ? 'green' : 'red'">
                        {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="p-4 table-actions">
                    <div class="flex gap-2">

                        <Link
                            v-if="cliente.activo"
                            :href="route('clientes.edit', cliente.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Button
                            :color="cliente.activo ? 'danger' : 'success'"
                            @click="toggleActivo(cliente.id, cliente.activo)"
                        >
                            {{ cliente.activo ? 'Desactivar' : 'Activar' }}
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="clientes.data.length === 0" class="table-empty">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen clientes registrados.
                </td>
            </tr>

        </Table>


        <Pagination :links="clientes.links" />

    </AppLayout>

</template>
