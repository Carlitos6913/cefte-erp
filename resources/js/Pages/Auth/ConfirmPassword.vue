<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    password: '',
})

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar contraseña" />

        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Confirma tu contraseña</h1>
            <p class="text-sm text-slate-400 mt-1.5">
                Esta es un área segura. Ingresa tu contraseña para continuar.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                    Contraseña
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autofocus
                    autocomplete="current-password"
                    class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                    :class="form.errors.password ? 'border-red-300 bg-red-50/50' : 'border-slate-200'"
                />
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                class="w-full"
            >
                {{ form.processing ? 'Verificando...' : 'Confirmar' }}
            </Button>
        </form>
    </GuestLayout>
</template>
