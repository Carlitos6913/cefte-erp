<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'


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

const correoBloqueado = computed(() => Boolean(props.empleado.email))

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

        <PageHeader title="Asignar usuario" subtitle="Crea una cuenta de acceso y asigna un rol al empleado.">
            <template #actions>
                <Link :href="route('empleados.index')">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al listado
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="max-w-5xl mx-auto">

            <FormLayout aside>

                <form
                    @submit.prevent="asignar"
                    class="space-y-5"
                >

                    <FormSection
                        title="Datos de la cuenta"
                        description="Información para el acceso al sistema."
                        tone="violet"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.928 0m11.928 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </template>

                        <div class="space-y-5">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <FormField
                                    label="Nombre visible en el sistema"
                                    forId="usuario-nombre"
                                    :error="form.errors.name"
                                >
                                    <input
                                        id="usuario-nombre"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Nombre completo"
                                        class="form-input"
                                        :class="{ 'is-invalid': form.errors.name }"
                                    />
                                </FormField>

                                <FormField
                                    label="Correo de acceso"
                                    forId="usuario-email"
                                    :error="correoBloqueado ? null : form.errors.email"
                                    :hint="correoBloqueado ? 'Proviene de la ficha del empleado y no puede modificarse aquí.' : null"
                                >
                                    <input
                                        id="usuario-email"
                                        v-model="form.email"
                                        type="email"
                                        :disabled="correoBloqueado"
                                        :readonly="correoBloqueado"
                                        placeholder="usuario@cefte.edu.bo"
                                        class="form-input"
                                        :class="{ 'is-invalid': !correoBloqueado && form.errors.email }"
                                    />
                                </FormField>

                            </div>

                        </div>
                    </FormSection>


                    <FormSection
                        title="Rol y acceso al sistema"
                        description="Define los permisos del usuario."
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </template>

                        <div class="space-y-5">

                            <FormField
                                label="Rol"
                                forId="usuario-rol"
                                :error="form.errors.role"
                                required
                            >
                                <select
                                    id="usuario-rol"
                                    v-model="form.role"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.role }"
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
                            </FormField>

                            <div
                                v-if="esNuevoRol"
                                class="border border-slate-200 rounded-xl p-5 bg-slate-50 space-y-5"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-700">
                                        Configurar nuevo rol
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Asigna nombre y módulos al nuevo rol.
                                    </p>
                                </div>

                                <FormField
                                    label="Nombre del rol"
                                    forId="usuario-nuevo-rol"
                                    :error="form.errors.nuevo_rol"
                                    required
                                >
                                    <input
                                        id="usuario-nuevo-rol"
                                        v-model="form.nuevo_rol"
                                        type="text"
                                        placeholder="Ej. Auxiliar, Contador, etc."
                                        class="form-input"
                                        :class="{ 'is-invalid': form.errors.nuevo_rol }"
                                    />
                                </FormField>

                                <FormField
                                    label="Módulos a los que tendrá acceso"
                                    :error="form.errors.modulos"
                                    hint="Selecciona los módulos que el usuario podrá visualizar en el sistema."
                                >
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <label
                                            v-for="(nombre, clave) in modulosDisponibles"
                                            :key="clave"
                                            class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition"
                                            :class="form.modulos.includes(clave)
                                                ? 'border-emerald-500 bg-emerald-50'
                                                : 'border-slate-200 bg-white hover:bg-slate-50'"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="clave"
                                                v-model="form.modulos"
                                                class="form-checkbox"
                                            />
                                            <span class="text-sm text-slate-700">{{ nombre }}</span>
                                        </label>
                                    </div>
                                </FormField>
                            </div>

                            <div
                                v-else-if="form.role && rolModulos.length"
                                class="border border-slate-200 rounded-xl p-4 bg-slate-50"
                            >
                                <p class="text-sm font-medium text-slate-600 mb-2">
                                    Módulos asignados a este rol:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="mod in rolModulos"
                                        :key="mod"
                                        class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"
                                    >
                                        {{ modulosDisponibles[mod] ?? mod }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-else-if="form.role && !rolModulos.length"
                                class="border border-slate-200 rounded-xl p-4 bg-slate-50"
                            >
                                <p class="text-sm text-slate-500">
                                    Este rol no tiene módulos configurados (acceso total).
                                </p>
                            </div>

                        </div>
                    </FormSection>


                    <FormActions>

                        <Link :href="route('empleados.index')">
                            <Button color="secondary" class="w-full sm:w-auto">
                                Cancelar
                            </Button>
                        </Link>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Asignando...' : 'Asignar usuario' }}
                        </Button>

                    </FormActions>

                </form>


                <template #aside>

                    <FormHelp
                        title="Ficha del empleado"
                        description="Resumen de la ficha seleccionada."
                        tone="slate"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        </template>

                        <dl class="space-y-3">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Nombre completo
                                </dt>
                                <dd class="text-sm font-medium text-slate-700">
                                    {{ empleado.first_name }} {{ empleado.first_surname }}
                                    {{ empleado.second_surname }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    CI / NIT
                                </dt>
                                <dd class="text-sm font-medium text-slate-700">
                                    {{ empleado.identity_card }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Teléfono
                                </dt>
                                <dd class="text-sm font-medium text-slate-700">
                                    {{ empleado.phone || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Profesión
                                </dt>
                                <dd class="text-sm font-medium text-slate-700">
                                    {{ empleado.profession || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Correo registrado
                                </dt>
                                <dd class="text-sm font-medium text-slate-700 break-words">
                                    {{ empleado.email || 'Sin correo registrado' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Estado
                                </dt>
                                <dd class="mt-1">
                                    <Badge :color="empleado.is_active ? 'green' : 'red'">
                                        {{ empleado.is_active ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </dd>
                            </div>
                        </dl>
                    </FormHelp>

                    <FormHelp
                        title="Contraseña inicial"
                        description="Se asigna automáticamente al crear la cuenta."
                        tone="amber"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </template>

                        <p>
                            La contraseña por defecto será el número de C.I. del empleado.
                            Para cambiarla, edite el usuario desde el módulo Usuarios.
                        </p>
                    </FormHelp>

                </template>

            </FormLayout>

        </div>

    </AppLayout>

</template>
