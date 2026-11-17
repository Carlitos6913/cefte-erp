<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar contraseña" />

        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-800">¿Olvidaste tu contraseña?</h1>
            <p class="text-sm text-slate-400 mt-1.5">
                Ingresa tu correo y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Correo electrónico
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@ejemplo.com"
                    class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    :class="form.errors.email ? 'border-red-300 bg-red-50/50' : 'border-slate-200'"
                />
                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                class="w-full"
            >
                {{ form.processing ? 'Enviando...' : 'Enviar enlace de restablecimiento' }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            <Link :href="route('login')" class="font-semibold text-emerald-600 hover:text-emerald-700">
                Volver al inicio de sesión
            </Link>
        </p>
    </GuestLayout>
</template>
