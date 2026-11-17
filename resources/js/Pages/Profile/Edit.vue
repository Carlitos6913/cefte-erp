<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import FormField from '@/Components/UI/FormField.vue'

const props = defineProps({
    userData: Object,
    mustVerifyEmail: Boolean,
    status: String,
})

const user = props.userData
const employee = user.employee

const nombreCompleto = employee
    ? [employee.first_name, employee.first_surname, employee.second_surname].filter(Boolean).join(' ')
    : user.name

const iniciales = employee
    ? [employee.first_name?.[0], employee.first_surname?.[0]].filter(Boolean).join('').toUpperCase()
    : user.name?.substring(0, 2).toUpperCase()

const form = useForm({
    name: user.name ?? '',
    email: user.email ?? '',
    employee: {
        first_name: employee?.first_name ?? '',
        first_surname: employee?.first_surname ?? '',
        second_surname: employee?.second_surname ?? '',
        identity_card: employee?.identity_card ?? '',
        phone: employee?.phone ?? '',
        address: employee?.address ?? '',
        profession: employee?.profession ?? '',
    },
})

const guardar = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    })
}

const reenviando = ref(false)

const reenviarVerificacion = () => {
    router.post(route('verification.send'), {}, {
        preserveScroll: true,
        onStart: () => (reenviando.value = true),
        onFinish: () => (reenviando.value = false),
    })
}

const fechaRegistro = user.created_at
    ? new Date(user.created_at).toLocaleDateString('es-BO', { year: 'numeric', month: 'long', day: 'numeric' })
    : '-'

const tiempoActivo = (() => {
    if (!user.created_at) return '-'
    const inicio = new Date(user.created_at)
    const ahora = new Date()
    const meses = (ahora.getFullYear() - inicio.getFullYear()) * 12 + (ahora.getMonth() - inicio.getMonth())
    if (meses < 1) return 'Menos de 1 mes'
    if (meses === 1) return '1 mes'
    if (meses < 12) return `${meses} meses`
    const años = Math.floor(meses / 12)
    const resto = meses % 12
    if (resto === 0) return años === 1 ? '1 año' : `${años} años`
    return `${años} años y ${resto} ${resto === 1 ? 'mes' : 'meses'}`
})()

const fechaVerificacion = user.email_verified_at
    ? new Date(user.email_verified_at).toLocaleDateString('es-BO', { year: 'numeric', month: 'long', day: 'numeric' })
    : null
</script>

<template>
    <AppLayout>

        <Head title="Mi perfil" />

        <div class="space-y-6">

            <!-- Tarjeta de perfil -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

                <!-- Banner -->
                <div class="relative h-36 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500" />
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 400 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="350" cy="20" r="80" stroke="white" stroke-width="0.5" opacity="0.3"/>
                            <circle cx="350" cy="20" r="60" stroke="white" stroke-width="0.5" opacity="0.2"/>
                            <circle cx="350" cy="20" r="40" stroke="white" stroke-width="0.5" opacity="0.1"/>
                            <circle cx="50" cy="130" r="60" stroke="white" stroke-width="0.5" opacity="0.2"/>
                            <circle cx="50" cy="130" r="40" stroke="white" stroke-width="0.5" opacity="0.15"/>
                            <line x1="0" y1="75" x2="400" y2="75" stroke="white" stroke-width="0.3" opacity="0.1"/>
                            <line x1="200" y1="0" x2="200" y2="150" stroke="white" stroke-width="0.3" opacity="0.1"/>
                        </svg>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent" />
                </div>

                <div class="px-6 pb-6 -mt-10 relative">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <!-- Avatar -->
                        <div class="w-20 h-20 rounded-2xl bg-white border-4 border-white shadow-lg flex items-center justify-center shrink-0">
                            <span class="text-2xl font-bold text-emerald-600">{{ iniciales }}</span>
                        </div>
                        <!-- Nombre y email -->
                        <div class="flex-1 min-w-0 pb-0.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-bold text-slate-800">{{ nombreCompleto }}</h2>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ user.rol ?? 'Sin rol' }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5">{{ user.email }}</p>
                        </div>
                    </div>

                    <!-- Info cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">

                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3.5">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">CI</p>
                                <p class="text-sm font-bold text-slate-700 truncate">{{ employee?.identity_card || '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3.5">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Teléfono</p>
                                <p class="text-sm font-bold text-slate-700 truncate">{{ employee?.phone || '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3.5">
                            <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Registro</p>
                                <p class="text-sm font-bold text-slate-700 truncate">{{ fechaRegistro }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3.5">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Antigüedad</p>
                                <p class="text-sm font-bold text-slate-700 truncate">{{ tiempoActivo }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Editar perfil + Seguridad en columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Formulario principal -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-800">Información personal</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Actualiza tus datos personales</p>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="guardar" class="space-y-5">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <FormField
                                    label="Nombre"
                                    forId="profile-name"
                                    :error="form.errors.name"
                                    required
                                >
                                    <input
                                        id="profile-name"
                                        v-model="form.name"
                                        type="text"
                                        class="form-input"
                                        :class="{ 'is-invalid': form.errors.name }"
                                        autocomplete="name"
                                        required
                                    />
                                </FormField>
                                <FormField
                                    label="Correo electrónico"
                                    forId="profile-email"
                                    :error="form.errors.email"
                                    hint="Si lo cambias, deberás verificarlo nuevamente."
                                    required
                                >
                                    <input
                                        id="profile-email"
                                        v-model="form.email"
                                        type="email"
                                        class="form-input"
                                        :class="{ 'is-invalid': form.errors.email }"
                                        autocomplete="username"
                                        required
                                    />
                                </FormField>
                            </div>

                            <!-- Datos del empleado -->
                            <div v-if="employee" class="border-t border-slate-100 pt-5">
                                <h4 class="text-sm font-semibold text-slate-600 mb-4">Datos del empleado</h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <FormField label="Nombre" forId="profile-first-name" optional>
                                        <input
                                            id="profile-first-name"
                                            v-model="form.employee.first_name"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                    <FormField label="Apellido paterno" forId="profile-first-surname" optional>
                                        <input
                                            id="profile-first-surname"
                                            v-model="form.employee.first_surname"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <FormField label="Apellido materno" forId="profile-second-surname" optional>
                                        <input
                                            id="profile-second-surname"
                                            v-model="form.employee.second_surname"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                    <FormField label="Cédula de identidad" forId="profile-identity-card" optional>
                                        <input
                                            id="profile-identity-card"
                                            v-model="form.employee.identity_card"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <FormField label="Teléfono" forId="profile-phone" optional>
                                        <input
                                            id="profile-phone"
                                            v-model="form.employee.phone"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                    <FormField label="Profesión" forId="profile-profession" optional>
                                        <input
                                            id="profile-profession"
                                            v-model="form.employee.profession"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                </div>

                                <div class="mt-4">
                                    <FormField label="Dirección" forId="profile-address" optional>
                                        <input
                                            id="profile-address"
                                            v-model="form.employee.address"
                                            type="text"
                                            class="form-input"
                                        />
                                    </FormField>
                                </div>
                            </div>

                            <div v-if="mustVerifyEmail && !fechaVerificacion" class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    <span>Tu correo no está verificado.</span>
                                    <button
                                        type="button"
                                        class="underline font-semibold hover:text-amber-800 ml-auto disabled:opacity-50"
                                        :disabled="reenviando"
                                        @click="reenviarVerificacion"
                                    >
                                        {{ reenviando ? 'Enviando...' : 'Reenviar' }}
                                    </button>
                                </div>

                                <p v-if="status === 'verification-link-sent'" class="mt-2 text-xs font-semibold text-emerald-600">
                                    Se envió un nuevo enlace de verificación a tu correo.
                                </p>
                            </div>

                            <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                                </Button>
                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Guardado
                                    </span>
                                </Transition>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Columna lateral -->
                <div class="space-y-6">

                    <!-- Seguridad -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="p-5 border-b border-slate-100">
                            <h3 class="text-base font-bold text-slate-800">Seguridad</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Cambia tu contraseña</p>
                        </div>
                        <div class="p-5">
                            <PasswordForm />
                        </div>
                    </div>

                    <!-- Estado de cuenta -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="p-5 border-b border-slate-100">
                            <h3 class="text-base font-bold text-slate-800">Estado de cuenta</h3>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Cuenta</span>
                                <span v-if="user.is_active" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                                    Activa
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 bg-red-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500" />
                                    Inactiva
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Email verificado</span>
                                <span v-if="fechaVerificacion" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Verificado
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-500 bg-amber-50 px-2.5 py-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Pendiente
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Rol asignado</span>
                                <span class="text-sm font-bold text-slate-700">{{ user.rol ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </AppLayout>
</template>

<script>
import PasswordForm from './Partials/PasswordForm.vue'
export default { components: { PasswordForm } }
</script>
