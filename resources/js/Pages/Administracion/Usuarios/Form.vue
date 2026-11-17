<script setup>

import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'

const props = defineProps({
    usuario: Object,
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
    editing: Boolean
})

const employeeActual = props.usuario?.employee ?? null

const form = useForm({
    employee_id: employeeActual?.id ?? '',
    first_name: employeeActual?.first_name ?? '',
    first_surname: employeeActual?.first_surname ?? '',
    second_surname: employeeActual?.second_surname ?? '',
    identity_card: employeeActual?.identity_card ?? '',
    phone: employeeActual?.phone ?? '',
    address: employeeActual?.address ?? '',
    profession: employeeActual?.profession ?? '',
    email_employee: employeeActual?.email ?? '',
    name: props.usuario?.name ?? '',
    email: props.usuario?.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.usuario?.roles[0]?.name ?? '',
    nuevo_rol: '',
    modulos: [],
})

const esNuevoRol = computed(() => form.role === '__nuevo__')

const usandoEmployeeExistente = computed(() => {
    if (props.editing) return true
    return form.employee_id !== ''
})

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

watch(() => form.employee_id, (newVal) => {
    if (newVal && !props.editing) {
        const emp = props.empleados.find(e => e.id == newVal)
        if (emp) {
            form.first_name = emp.first_name
            form.first_surname = emp.first_surname
            form.second_surname = emp.second_surname ?? ''
            form.identity_card = emp.identity_card
            form.phone = emp.phone ?? ''
            form.address = emp.address ?? ''
            form.profession = emp.profession ?? ''
            form.email_employee = emp.email ?? ''
            form.name = `${emp.first_name} ${emp.first_surname}`
            form.email = emp.email ?? ''
        }
    }
})

function guardar(){

    if(props.editing){
        form.put(route('usuarios.update', props.usuario.id))
    }else{
        form.post(route('usuarios.store'))
    }

}

</script>

<template>

<AppLayout>
    <Head :title="editing ? 'Editar usuario' : 'Nuevo usuario'" />

    <div class="max-w-5xl mx-auto">

        <PageHeader :title="editing ? 'Editar usuario' : 'Nuevo usuario'" :subtitle="editing ? 'Modifica la información del usuario.' : 'Registra un nuevo usuario en el sistema.'">
            <template #actions>
                <Link :href="route('usuarios.index')">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al listado
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <FormLayout aside>

            <form @submit.prevent="guardar" class="space-y-5">

                <FormSection
                    v-if="!editing && empleados.length > 0"
                    title="Empleado"
                    description="Asigna un empleado existente o crea uno nuevo."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </template>

                    <div class="space-y-5">

                        <FormField
                            label="Seleccionar empleado existente"
                            forId="usuario-empleado"
                            hint="Elige un empleado para completar sus datos automáticamente."
                        >
                            <select
                                id="usuario-empleado"
                                v-model="form.employee_id"
                                class="form-select"
                            >
                                <option value="">-- Crear nuevo empleado --</option>
                                <option
                                    v-for="emp in empleados"
                                    :key="emp.id"
                                    :value="emp.id"
                                >
                                    {{ emp.first_name }} {{ emp.first_surname }} - CI: {{ emp.identity_card }}
                                </option>
                            </select>
                        </FormField>

                        <div v-if="form.employee_id" class="flex items-start gap-3 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-emerald-800">
                                    Empleado seleccionado. Los datos se completarán automáticamente.
                                </p>
                            </div>
                        </div>

                    </div>
                </FormSection>

                <FormSection
                    title="Datos del empleado"
                    description="Información personal del empleado."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <FormField
                            label="Nombre"
                            forId="usuario-first-name"
                            :error="form.errors.first_name"
                            required
                        >
                            <input
                                id="usuario-first-name"
                                v-model="form.first_name"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Nombre"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.first_name }"
                            />
                        </FormField>

                        <FormField
                            label="Apellido paterno"
                            forId="usuario-first-surname"
                            :error="form.errors.first_surname"
                            required
                        >
                            <input
                                id="usuario-first-surname"
                                v-model="form.first_surname"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Apellido paterno"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.first_surname }"
                            />
                        </FormField>

                        <FormField
                            label="Apellido materno"
                            forId="usuario-second-surname"
                            :error="form.errors.second_surname"
                            optional
                        >
                            <input
                                id="usuario-second-surname"
                                v-model="form.second_surname"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Apellido materno"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.second_surname }"
                            />
                        </FormField>

                        <FormField
                            label="CI / NIT"
                            forId="usuario-identity-card"
                            :error="form.errors.identity_card"
                            required
                        >
                            <input
                                id="usuario-identity-card"
                                v-model="form.identity_card"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Ej. 8492011 CB"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.identity_card }"
                            />
                        </FormField>

                        <FormField
                            label="Teléfono"
                            forId="usuario-phone"
                            :error="form.errors.phone"
                            optional
                        >
                            <input
                                id="usuario-phone"
                                v-model="form.phone"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Ej. 71234567"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.phone }"
                            />
                        </FormField>

                        <FormField
                            label="Profesión / Oficio"
                            forId="usuario-profession"
                            :error="form.errors.profession"
                            optional
                        >
                            <input
                                id="usuario-profession"
                                v-model="form.profession"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Ej. Técnico Superior en Agroindustria"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.profession }"
                            />
                        </FormField>

                        <FormField
                            label="Dirección"
                            forId="usuario-address"
                            :error="form.errors.address"
                            class="md:col-span-2"
                            optional
                        >
                            <input
                                id="usuario-address"
                                v-model="form.address"
                                type="text"
                                :disabled="usandoEmployeeExistente"
                                placeholder="Av. Principal S/N, Eterazama"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.address }"
                            />
                        </FormField>

                        <FormField
                            label="Correo del empleado"
                            forId="usuario-email-employee"
                            :error="form.errors.email_employee"
                            class="md:col-span-2"
                            optional
                        >
                            <input
                                id="usuario-email-employee"
                                v-model="form.email_employee"
                                type="email"
                                :disabled="usandoEmployeeExistente"
                                placeholder="correo@ejemplo.com"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.email_employee }"
                            />
                        </FormField>

                    </div>
                </FormSection>

                <FormSection
                    title="Datos de acceso"
                    description="Credenciales para ingresar al sistema."
                    tone="violet"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <FormField
                            label="Nombre visible en el sistema"
                            forId="usuario-name"
                            :error="form.errors.name"
                        >
                            <input
                                id="usuario-name"
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
                            :error="form.errors.email"
                            required
                        >
                            <input
                                id="usuario-email"
                                v-model="form.email"
                                type="email"
                                placeholder="usuario@cefte.edu.bo"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.email }"
                            />
                        </FormField>

                    </div>
                </FormSection>

                <FormSection
                    v-if="editing"
                    title="Cambiar contraseña"
                    description="Deja los campos vacíos para mantener la contraseña actual."
                    tone="amber"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <FormField
                            label="Nueva contraseña"
                            forId="usuario-password"
                            :error="form.errors.password"
                            hint="Mínimo 8 caracteres."
                            optional
                        >
                            <input
                                id="usuario-password"
                                v-model="form.password"
                                type="password"
                                placeholder="Mínimo 8 caracteres"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.password }"
                            />
                        </FormField>

                        <FormField
                            label="Confirmar contraseña"
                            forId="usuario-password-confirmation"
                            optional
                        >
                            <input
                                id="usuario-password-confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Repite la contraseña"
                                class="form-input"
                            />
                        </FormField>

                    </div>
                </FormSection>

                <FormSection
                    v-if="!editing"
                    title="Contraseña"
                    description="Se genera automáticamente."
                    tone="amber"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </template>

                    <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <svg class="w-5 h-5 text-slate-500 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-slate-600">
                            La contraseña se define automáticamente a partir del número de C.I. del empleado.
                        </p>
                    </div>
                </FormSection>

                <FormSection
                    title="Rol y permisos"
                    description="Define el rol y los accesos del usuario."
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
                            forId="usuario-role"
                            :error="form.errors.role"
                            required
                        >
                            <select
                                id="usuario-role"
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
                                <h3 class="text-sm font-semibold text-slate-700">Configurar nuevo rol</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Asigna nombre y módulos al nuevo rol.</p>
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label
                                        v-for="(nombre, clave) in modulosDisponibles"
                                        :key="clave"
                                        class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition"
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
                            <p class="text-sm text-slate-400">
                                Este rol no tiene módulos configurados.
                            </p>
                        </div>

                    </div>
                </FormSection>

                <FormActions>
                    <Link :href="route('usuarios.index')">
                        <Button color="secondary" class="w-full sm:w-auto">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing
                            ? 'Guardando...'
                            : editing
                                ? 'Actualizar usuario'
                                : 'Guardar usuario'
                        }}
                    </Button>
                </FormActions>

            </form>

            <template #aside>

                <FormHelp
                    title="Resumen del registro"
                    description="Revisa la información antes de guardar."
                    tone="slate"
                >
                    <template #icon>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </template>

                    <dl class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Modo</dt>
                            <dd class="font-semibold text-slate-700">{{ editing ? 'Edición' : 'Nuevo usuario' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Empleado</dt>
                            <dd class="font-semibold text-slate-700 text-right">
                                {{ (form.first_name + ' ' + form.first_surname).trim() || 'Sin definir' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Rol</dt>
                            <dd class="font-semibold text-slate-700 text-right">
                                {{ form.role === '__nuevo__' ? (form.nuevo_rol || 'Nuevo rol') : (form.role || 'Sin asignar') }}
                            </dd>
                        </div>
                        <div v-if="esNuevoRol" class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Módulos</dt>
                            <dd class="font-semibold text-slate-700">{{ form.modulos.length }}</dd>
                        </div>
                    </dl>
                </FormHelp>

                <FormHelp
                    title="Acceso y seguridad"
                    description="Buenas prácticas al gestionar usuarios."
                    tone="amber"
                >
                    <template #icon>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </template>

                    <ul class="space-y-2.5">
                        <li class="flex gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>El correo de acceso debe ser único en el sistema.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>La contraseña inicial se genera desde la C.I. del empleado.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>Selecciona un rol existente o crea uno nuevo con sus módulos.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>Revisa los módulos asignados antes de guardar.</span>
                        </li>
                    </ul>
                </FormHelp>

            </template>

        </FormLayout>

    </div>
</AppLayout>

</template>
