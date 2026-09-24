<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'


const props = defineProps({
    empleado: Object,
    roles: Array,
    modulosDisponibles: Object,
})


const form = useForm({
    name: `${props.empleado.first_name} ${props.empleado.first_surname}`,
    email: props.empleado.email ?? '',
    role: '',
    password: '',
    nuevo_rol: '',
    modulos: [],
})


const esNuevoRol = computed(() => form.role === '__nuevo__')

const rolModulos = computed(() => {
    const rol = props.roles.find(r => r.name === form.role)
    if (!rol?.modulos) return []
    if (Array.isArray(rol.modulos)) return rol.modulos
    try {
        return JSON.parse(rol.modulos)
    } catch {
        return []
    }
})


const asignar = () => {
    form.post(route('empleados.usuario.store', props.empleado.id))
}

</script>


<template>

    <Head title="Asignar usuario" />

    <AppLayout>

        <div class="max-w-2xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('empleados.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Asignar usuario
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Crea una cuenta de acceso y asigna un rol al empleado.
                </p>

            </div>


            <!-- Resumen del empleado -->

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">

                <h2 class="text-lg font-semibold text-slate-800 mb-4">
                    Datos del empleado
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div>
                        <span class="text-slate-500">Nombre completo:</span>
                        <p class="font-medium text-slate-800">
                            {{ empleado.first_name }} {{ empleado.first_surname }}
                            {{ empleado.second_surname }}
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-500">CI / NIT:</span>
                        <p class="font-medium text-slate-800">
                            {{ empleado.identity_card }}
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-500">Teléfono:</span>
                        <p class="font-medium text-slate-800">
                            {{ empleado.phone || '-' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-500">Profesión:</span>
                        <p class="font-medium text-slate-800">
                            {{ empleado.profession || '-' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-500">Estado:</span>
                        <p class="mt-1">
                            <Badge :color="empleado.is_active ? 'green' : 'red'">
                                {{ empleado.is_active ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </p>
                    </div>

                </div>

            </div>


            <form
                @submit.prevent="asignar"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >

                <!-- Datos de la cuenta -->

                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">
                        Datos de la cuenta
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Nombre visible en el sistema
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                                placeholder="Nombre completo"
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Correo de acceso
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                                placeholder="usuario@cefte.edu.bo"
                            />
                            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                {{ form.errors.email }}
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Contraseña -->

                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-lg">
                    <svg class="w-5 h-5 text-slate-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800 mb-1">
                            Contraseña
                        </h2>
                        <p class="text-sm text-slate-500">
                            La contraseña por defecto será el número de C.I. del empleado.
                            Para cambiarla, edite el usuario desde el módulo Usuarios.
                        </p>
                    </div>
                </div>


                <!-- Rol y módulos -->

                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">
                        Rol y acceso al sistema
                    </h2>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Rol <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        >
                            <option value="">Seleccione un rol</option>
                            <option
                                v-for="rol in roles"
                                :key="rol.id"
                                :value="rol.name"
                            >
                                {{ rol.name }}
                            </option>
                            <option value="__nuevo__">+ Crear nuevo rol</option>
                        </select>
                        <p v-if="form.errors.role" class="text-sm text-red-500 mt-1">
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <!-- Formulario de nuevo rol -->

                    <div
                        v-if="esNuevoRol"
                        class="border border-slate-200 rounded-lg p-5 bg-slate-50"
                    >
                        <h3 class="text-sm font-semibold text-slate-700 mb-4">
                            Configurar nuevo rol
                        </h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Nombre del rol <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.nuevo_rol"
                                type="text"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                                placeholder="Ej. Auxiliar, Contador, etc."
                            />
                            <p v-if="form.errors.nuevo_rol" class="text-sm text-red-500 mt-1">
                                {{ form.errors.nuevo_rol }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">
                                Módulos a los que tendrá acceso
                            </label>
                            <p class="text-xs text-slate-500 mb-3">
                                Selecciona los módulos que el usuario podrá visualizar en el sistema.
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="(nombre, clave) in modulosDisponibles"
                                    :key="clave"
                                    class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition"
                                    :class="form.modulos.includes(clave)
                                        ? 'border-emerald-500 bg-emerald-50'
                                        : 'border-slate-200 bg-white hover:bg-slate-50'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="clave"
                                        v-model="form.modulos"
                                        class="rounded border-slate-300 text-emerald-600
                                               focus:ring-emerald-500"
                                    />
                                    <span class="text-sm text-slate-700">{{ nombre }}</span>
                                </label>
                            </div>

                            <p v-if="form.errors.modulos" class="text-sm text-red-500 mt-2">
                                {{ form.errors.modulos }}
                            </p>
                        </div>
                    </div>

                    <!-- Info del rol existente -->

                    <div
                        v-else-if="form.role && rolModulos.length"
                        class="border border-slate-200 rounded-lg p-4 bg-slate-50"
                    >
                        <p class="text-sm font-medium text-slate-600 mb-2">
                            Módulos asignados a este rol:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="mod in rolModulos"
                                :key="mod"
                                class="px-3 py-1 rounded-full text-xs font-medium
                                       bg-emerald-100 text-emerald-700"
                            >
                                {{ modulosDisponibles[mod] ?? mod }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-else-if="form.role && !rolModulos.length"
                        class="border border-slate-200 rounded-lg p-4 bg-slate-50"
                    >
                        <p class="text-sm text-slate-400">
                            Este rol no tiene módulos configurados (acceso total).
                        </p>
                    </div>

                </div>


                <!-- Botones -->

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">

                    <Link :href="route('empleados.index')">
                        <Button color="secondary">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Asignando...' : 'Asignar usuario' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
