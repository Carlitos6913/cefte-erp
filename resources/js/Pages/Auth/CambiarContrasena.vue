<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.put(route('password.cambio.update'), {
        onFinish: () => form.reset('current_password', 'password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Cambiar contraseña" />

    <div class="min-h-screen bg-slate-950 flex">

        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">

            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950"></div>

            <div class="relative z-10 flex flex-col justify-center px-16 text-white">

                <div class="mb-8">
                    <div class="text-5xl font-black tracking-tight">
                        CEFTE
                    </div>

                    <div class="text-emerald-400 text-xl font-semibold tracking-widest">
                        ERP
                    </div>
                </div>

                <h1 class="text-4xl font-bold leading-tight max-w-lg">
                    Crea una nueva contraseña
                </h1>

                <p class="mt-6 text-slate-400 text-lg max-w-md">
                    Por seguridad debes reemplazar tu contraseña temporal antes de
                    continuar usando el sistema.
                </p>

            </div>
        </div>

        <div class="w-full lg:w-1/2 bg-slate-50 flex items-center justify-center px-6">

            <div class="w-full max-w-md">

                <div class="text-center mb-8">

                    <div class="lg:hidden mb-6">
                        <div class="text-4xl font-black text-slate-900">
                            CEFTE
                        </div>

                        <div class="text-emerald-600 font-bold tracking-widest">
                            ERP
                        </div>
                    </div>

                    <h2 class="text-3xl font-bold text-slate-900">
                        Cambio de contraseña
                    </h2>

                    <p class="mt-2 text-slate-500">
                        Define una contraseña segura y personal
                    </p>

                </div>

                <form
                    @submit.prevent="submit"
                    class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8"
                >

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-700">
                            Contraseña actual *
                        </label>

                        <input
                            id="current_password"
                            type="password"
                            v-model="form.current_password"
                            class="mt-2 block w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                            required
                            autocomplete="current-password"
                        />

                        <p v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <label for="password" class="block text-sm font-medium text-slate-700">
                            Nueva contraseña *
                        </label>

                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="mt-2 block w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                            required
                            autocomplete="new-password"
                        />

                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                            Confirmar nueva contraseña *
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="mt-2 block w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"
                            required
                            autocomplete="new-password"
                        />

                        <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <PrimaryButton
                        type="submit"
                        class="mt-7 w-full justify-center"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Cambiar contraseña' }}
                    </PrimaryButton>

                </form>

            </div>

        </div>

    </div>
</template>