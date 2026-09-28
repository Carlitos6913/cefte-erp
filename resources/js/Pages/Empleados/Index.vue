<script setup>

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    empleados: Object,
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

const headers = [
    'Nombre completo',
    'CI',
    'Teléfono',
    'Profesión',
    'Usuario',
    'Estado',
    'Acciones'
]


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

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    Empleados
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Gestión del personal registrado en el sistema
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar por nombre, CI, teléfono..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />

                <Link :href="route('empleados.create')">
                    <Button>
                        Nuevo empleado
                    </Button>
                </Link>

            </div>

        </div>


        <Table :headers="headers">

            <tr
                v-for="empleado in empleados.data"
                :key="empleado.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
                :class="{ 'opacity-40': !empleado.is_active }"
            >

                <td data-label="Nombre completo" class="p-4 font-medium text-slate-800">
                    <div>{{ empleado.first_name }} {{ empleado.first_surname }}</div>
                    <div
                        v-if="empleado.second_surname"
                        class="text-xs text-slate-500"
                    >
                        {{ empleado.second_surname }}
                    </div>
                </td>

                <td data-label="CI" class="p-4 text-slate-600">
                    {{ empleado.identity_card || '-' }}
                </td>

                <td data-label="Teléfono" class="p-4 text-slate-600">
                    {{ empleado.phone || '-' }}
                </td>

                <td data-label="Profesión" class="p-4 text-slate-600">
                    {{ empleado.profession || '-' }}
                </td>

                <td data-label="Usuario" class="p-4">
                    <template v-if="empleado.user">
                        <Badge color="blue">
                            {{ empleado.user.roles.length ? empleado.user.roles[0].name : 'Sin rol' }}
                        </Badge>
                    </template>
                    <template v-else>
                        <Badge color="gray">
                            Sin usuario
                        </Badge>
                    </template>
                </td>

                <td data-label="Estado" class="p-4">
                    <Badge :color="empleado.is_active ? 'green' : 'red'">
                        {{ empleado.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                </td>

                <td class="p-4 table-actions">
                    <div class="flex flex-wrap gap-2">

                        <Link
                            v-if="empleado.is_active"
                            :href="route('empleados.edit', empleado.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Link
                            v-if="empleado.is_active && !empleado.user"
                            :href="route('empleados.usuario', empleado.id)"
                        >
                            <Button color="primary">
                                Asignar usuario
                            </Button>
                        </Link>

                        <Button
                            :color="empleado.is_active ? 'danger' : 'success'"
                            @click="toggleActivo(empleado.id, empleado.is_active)"
                        >
                            {{ empleado.is_active ? 'Desactivar' : 'Activar' }}
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="empleados.data.length === 0" class="table-empty">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen empleados registrados.
                </td>
            </tr>

        </Table>


        <Pagination :links="empleados.links" />

    </AppLayout>

</template>
