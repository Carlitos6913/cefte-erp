<script setup>
import { Head, useForm } from '@inertiajs/vue3'

import Button from '@/Components/UI/Button.vue'
import FormField from '@/Components/UI/FormField.vue'

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.put(route('password.cambio.update'))
}
</script>

<template>
    <Head title="Cambiar contraseña" />

    <div class="min-h-screen bg-slate-950 flex">

        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">

            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950"></div>
            <div class="absolute -top-32 -left-24 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
            <div class="absolute -bottom-40 right-0 w-96 h-96 rounded-full bg-teal-400/10 blur-3xl"></div>

            <div class="relative z-10 flex flex-col justify-center px-16 text-white">

                <div class="mb-10">
                    <div class="text-5xl font-black tracking-tight">
                        CEFTE
                    </div>
                </div>

                <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>

                <h1 class="text-4xl font-bold leading-tight max-w-lg">
                    Protege tu cuenta con una nueva contraseña
                </h1>

                <p class="mt-6 text-slate-400 text-lg max-w-md">
                    Estás usando una contraseña temporal. Define una contraseña
                    personal antes de continuar usando el sistema.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-slate-400">
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        Al menos 8 caracteres
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        Diferente a tu contraseña actual
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        Fácil de recordar, difícil de adivinar
                    </li>
                </ul>

            </div>
        </div>

        <div class="w-full lg:w-1/2 bg-slate-50 flex items-center justify-center px-6 py-10">

            <div class="w-full max-w-md">

                <div class="text-center mb-8">

                    <div class="lg:hidden mb-6">
                        <div class="text-4xl font-black text-slate-900">
                            CEFTE
                        </div>
                    </div>

                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900">
                        Cambio de contraseña
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Define una contraseña segura y personal
                    </p>

                </div>

                <form
                    @submit.prevent="submit"
                    class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 space-y-5"
                >

                    <FormField
                        label="Contraseña actual"
                        forId="current_password"
                        :error="form.errors.current_password"
                        required
                    >
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.current_password }"
                            required
                            autofocus
                            autocomplete="current-password"
                            placeholder="Ingresa tu contraseña temporal"
                        />
                    </FormField>

                    <FormField
                        label="Nueva contraseña"
                        forId="password"
                        :error="form.errors.password"
                        hint="Mínimo 8 caracteres."
                        required
                    >
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.password }"
                            required
                            autocomplete="new-password"
                            placeholder="Crea una nueva contraseña"
                        />
                    </FormField>

                    <FormField
                        label="Confirmar nueva contraseña"
                        forId="password_confirmation"
                        :error="form.errors.password_confirmation"
                        required
                    >
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="form-input"
                            :class="{ 'is-invalid': form.errors.password_confirmation }"
                            required
                            autocomplete="new-password"
                            placeholder="Repite la nueva contraseña"
                        />
                    </FormField>

                    <Button
                        type="submit"
                        class="w-full justify-center"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Cambiar contraseña' }}
                    </Button>

                    <p class="text-center text-xs text-slate-400">
                        Al cambiar tu contraseña serás redirigido al panel principal.
                    </p>

                </form>

            </div>

        </div>

    </div>
</template>
