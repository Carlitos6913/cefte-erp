<script setup>

import { useForm, Link } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    usuario: Object,
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
    editing: Boolean
})

const employeeActual = props.usuario?.employee ?? null

const form = useForm({

    // Empleado - selección existente
    employee_id: employeeActual?.id ?? '',

    // Empleado - datos (para crear nuevo o editar existente)
    first_name: employeeActual?.first_name ?? '',
    first_surname: employeeActual?.first_surname ?? '',
    second_surname: employeeActual?.second_surname ?? '',
    identity_card: employeeActual?.identity_card ?? '',
    phone: employeeActual?.phone ?? '',
    address: employeeActual?.address ?? '',
    profession: employeeActual?.profession ?? '',
    email_employee: employeeActual?.email ?? '',

    // Usuario
    name: props.usuario?.name ?? '',
    email: props.usuario?.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.usuario?.roles[0]?.name ?? '',

    // Nuevo rol
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

// Auto-fill cuando se selecciona un employee existente
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

<div class="max-w-4xl">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800">
            {{ editing ? 'Editar Usuario' : 'Nuevo Usuario' }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            {{ editing ? 'Modifica la información del usuario.' : 'Registra un nuevo usuario en el sistema.' }}
        </p>
    </div>

    <form @submit.prevent="guardar">

        <!-- Selección de empleado -->
        <div v-if="!editing && empleados.length > 0" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">
                Empleado
            </h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Seleccionar empleado existente
                </label>
                <select
                    v-model="form.employee_id"
                    class="w-full rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
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
            </div>

            <div v-if="form.employee_id" class="flex items-start gap-3 p-4 bg-emerald-50 rounded-lg">
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

        <!-- Datos del empleado -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">
                Datos del empleado
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Nombre"
                    />
                    <p v-if="form.errors.first_name" class="text-sm text-red-500 mt-1">
                        {{ form.errors.first_name }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Apellido paterno <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.first_surname"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Apellido paterno"
                    />
                    <p v-if="form.errors.first_surname" class="text-sm text-red-500 mt-1">
                        {{ form.errors.first_surname }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Apellido materno
                    </label>
                    <input
                        v-model="form.second_surname"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Apellido materno"
                    />
                    <p v-if="form.errors.second_surname" class="text-sm text-red-500 mt-1">
                        {{ form.errors.second_surname }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        CI / NIT <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.identity_card"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Ej. 8492011 CB"
                    />
                    <p v-if="form.errors.identity_card" class="text-sm text-red-500 mt-1">
                        {{ form.errors.identity_card }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Teléfono
                    </label>
                    <input
                        v-model="form.phone"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Ej. 71234567"
                    />
                    <p v-if="form.errors.phone" class="text-sm text-red-500 mt-1">
                        {{ form.errors.phone }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Profesión / Oficio
                    </label>
                    <input
                        v-model="form.profession"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Ej. Técnico Superior en Agroindustria"
                    />
                    <p v-if="form.errors.profession" class="text-sm text-red-500 mt-1">
                        {{ form.errors.profession }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Dirección
                    </label>
                    <input
                        v-model="form.address"
                        type="text"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="Av. Principal S/N, Eterazama"
                    />
                    <p v-if="form.errors.address" class="text-sm text-red-500 mt-1">
                        {{ form.errors.address }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Correo del empleado
                    </label>
                    <input
                        v-model="form.email_employee"
                        type="email"
                        :disabled="usandoEmployeeExistente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        :class="{ 'bg-slate-50 text-slate-500': usandoEmployeeExistente }"
                        placeholder="correo@ejemplo.com"
                    />
                    <p v-if="form.errors.email_employee" class="text-sm text-red-500 mt-1">
                        {{ form.errors.email_employee }}
                    </p>
                </div>

            </div>
        </div>


        <!-- Datos de la cuenta -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">
                Datos de la cuenta
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
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
                    <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Correo de acceso <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        placeholder="usuario@cefte.edu.bo"
                    />
                    <p v-if="form.errors.email" class="text-sm text-red-500 mt-1">
                        {{ form.errors.email }}
                    </p>
                </div>

            </div>
        </div>


        <!-- Contraseña -->
        <div v-if="editing" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">
                Cambiar contraseña
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nueva contraseña (dejar vacío para mantener)
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        placeholder="Mínimo 8 caracteres"
                    />
                    <p v-if="form.errors.password" class="text-sm text-red-500 mt-1">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Confirmar contraseña
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                        placeholder="Repite la contraseña"
                    />
                </div>

            </div>
        </div>

        <div v-if="!editing" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-1">
                        Contraseña
                    </h2>
                    <p class="text-sm text-slate-500">
                        La contraseña se define automáticamente a partir del número de C.I. del empleado.
                    </p>
                </div>
            </div>
        </div>


        <!-- Rol y módulos -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
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
                    Este rol no tiene módulos configurados.
                </p>
            </div>
        </div>


        <!-- Botones -->
        <div class="flex justify-end gap-3">
            <Link :href="route('usuarios.index')">
                <Button color="secondary">
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
                        ? 'Actualizar Usuario'
                        : 'Guardar Usuario'
                }}
            </Button>
        </div>

    </form>

</div>

</template>
