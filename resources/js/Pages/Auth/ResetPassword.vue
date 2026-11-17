<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer contraseña" />

        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Restablecer contraseña</h1>
            <p class="text-sm text-slate-400 mt-1.5">
                Define una nueva contraseña para tu cuenta.
            </p>
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
                    autocomplete="username"
                    class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    :class="form.errors.email ? 'border-red-300 bg-red-50/50' : 'border-slate-200'"
                />
                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Nueva contraseña
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autofocus
                    autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    :class="form.errors.password ? 'border-red-300 bg-red-50/50' : 'border-slate-200'"
                />
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Confirmar contraseña
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    :class="form.errors.password_confirmation ? 'border-red-300 bg-red-50/50' : 'border-slate-200'"
                />
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ form.errors.password_confirmation }}</p>
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                class="w-full"
            >
                {{ form.processing ? 'Guardando...' : 'Restablecer contraseña' }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            <Link :href="route('login')" class="font-semibold text-emerald-600 hover:text-emerald-700">
                Volver al inicio de sesión
            </Link>
        </p>
    </GuestLayout>
</template>
