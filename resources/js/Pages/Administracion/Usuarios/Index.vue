<script setup>

import { Head, Link, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

import { ref, watch } from 'vue'


const props = defineProps({
    usuarios: Object,
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
    'Nombre',
    'CI',
    'Teléfono',
    'Correo',
    'Rol',
    'Estado',
    'Acciones'
]


const colorRol = (rol) => {
    switch (rol) {
        case 'Administrador': return 'red'
        case 'Gerente': return 'yellow'
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

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">

            <div>

                <h1 class="text-3xl font-bold text-slate-800">
                    Usuarios
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Gestión de usuarios y accesos al sistema
                </p>

            </div>


            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <!-- Buscador -->

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar por nombre, CI, correo..."
                    class="w-full sm:w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />


                <!-- Nuevo usuario -->

                <Link :href="route('usuarios.create')">

                    <Button>
                        Nuevo Usuario
                    </Button>

                </Link>

            </div>

        </div>


        <!-- Tabla -->

        <Table :headers="headers">

            <tr
                v-for="usuario in usuarios.data"
                :key="usuario.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
                :class="{ 'opacity-40': !usuario.is_active }"
            >

                <!-- Nombre -->

                <td class="p-4 font-medium text-slate-800">
                    <div>{{ usuario.name }}</div>
                    <div
                        v-if="usuario.employee"
                        class="text-xs text-slate-500"
                    >
                        {{ [usuario.employee.first_surname, usuario.employee.second_surname].filter(Boolean).join(' ') }}
                    </div>
                </td>


                <!-- CI -->

                <td class="p-4 text-slate-600">
                    {{ usuario.employee?.identity_card || '-' }}
                </td>


                <!-- Teléfono -->

                <td class="p-4 text-slate-600">
                    {{ usuario.employee?.phone || '-' }}
                </td>


                <!-- Correo -->

                <td class="p-4 text-slate-600">
                    {{ usuario.email }}
                </td>


                <!-- Rol -->

                <td class="p-4">

                    <Badge :color="colorRol(usuario.roles.length ? usuario.roles[0].name : '')">

                        {{
                            usuario.roles.length
                                ? usuario.roles[0].name
                                : 'Sin rol'
                        }}

                    </Badge>

                </td>


                <!-- Estado -->

                <td class="p-4">

                    <Badge :color="usuario.is_active ? 'green' : 'red'">
                        {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>

                </td>


                <!-- Acciones -->

                <td class="p-4">

                    <div class="flex gap-2">

                        <!-- Editar -->

                        <Link
                            v-if="usuario.is_active"
                            :href="route('usuarios.edit', usuario.id)"
                        >

                            <Button color="info">
                                Editar
                            </Button>

                        </Link>


                        <!-- Desactivar / Activar -->

                        <Button
                            :color="usuario.is_active ? 'danger' : 'success'"
                            @click="toggleActivo(usuario.id, usuario.is_active)"
                        >
                            {{ usuario.is_active ? 'Desactivar' : 'Activar' }}
                        </Button>

                    </div>

                </td>

            </tr>


            <!-- Sin resultados -->

            <tr v-if="usuarios.data.length === 0">

                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No se encontraron usuarios.
                </td>

            </tr>

        </Table>


        <!-- Paginación -->

        <div
            v-if="usuarios.links && usuarios.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >

            <template
                v-for="link in usuarios.links"
                :key="link.label"
            >

                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
                    class="px-3 py-2 rounded-lg border text-sm transition"
                    :class="link.active
                        ? 'bg-emerald-600 text-white border-emerald-600'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                />

                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-2 rounded-lg border border-slate-200
                           text-sm text-slate-300 bg-slate-50"
                />

            </template>

        </div>

    </AppLayout>

</template>
