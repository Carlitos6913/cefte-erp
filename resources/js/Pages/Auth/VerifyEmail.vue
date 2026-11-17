<script setup>
import { computed } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Button from '@/Components/UI/Button.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    status: {
        type: String,
    },
})

const form = useForm({})

const submit = () => {
    form.post(route('verification.send'))
}

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
)
</script>

<template>
    <GuestLayout>
        <Head title="Verificar correo electrónico" />

        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Verifica tu correo</h1>
            <p class="text-sm text-slate-400 mt-1.5">
                Gracias por registrarte. Antes de continuar, confirma tu correo electrónico con el enlace que enviamos a tu bandeja de entrada.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-700"
        >
            Se envió un nuevo enlace de verificación al correo que registraste.
        </div>

        <form @submit.prevent="submit">
            <Button
                type="submit"
                :disabled="form.processing"
                class="w-full"
            >
                {{ form.processing ? 'Enviando...' : 'Reenviar verificación' }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="font-semibold text-emerald-600 hover:text-emerald-700"
            >
                Cerrar sesión
            </Link>
        </p>
    </GuestLayout>
</template>
