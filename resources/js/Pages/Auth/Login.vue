<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="min-h-screen bg-slate-950 flex">



        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">

            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950"></div>

            <div class="relative z-10 flex flex-col justify-center px-16 text-white">

                <div class="mb-8">
                    <div class="text-5xl font-black tracking-tight">
                        CEFTE
                    </div>
                </div>

                <h1 class="text-4xl font-bold leading-tight max-w-lg">
                    Sistema de gestión
                    de produccion y ventas
                    de la planta.
                </h1>

                <p class="mt-6 text-slate-400 text-lg max-w-md">
                    Control de inventarios, ventas, pagos y
                    operaciones administrativas desde un solo lugar.
                </p>

                <div class="mt-10 flex gap-3">
                    <div class="h-1 w-16 bg-emerald-500 rounded"></div>
                    <div class="h-1 w-4 bg-emerald-700 rounded"></div>
                    <div class="h-1 w-2 bg-emerald-900 rounded"></div>
                </div>

            </div>
        </div>




        <div class="w-full lg:w-1/2 bg-slate-50 flex items-center justify-center px-6">

            <div class="w-full max-w-md">

                <div class="text-center mb-8">

                    <div class="lg:hidden mb-6">
                        <div class="text-4xl font-black text-slate-900">
                            CEFTE
                        </div>
                    </div>

                    <h2 class="text-3xl font-bold text-slate-900">
                        Bienvenido
                    </h2>

                    <p class="mt-2 text-slate-500">
                        Ingresa tus credenciales para continuar
                    </p>

                </div>


                <div
                    v-if="status"
                    class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700"
                >
                    {{ status }}
                </div>


                <form
                    @submit.prevent="submit"
                    class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8"
                >



                    <div>
                        <InputLabel
                            for="email"
                            value="Correo electrónico"
                        />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />

                        <InputError
                            class="mt-2"
                            :message="form.errors.email"
                        />
                    </div>




                    <div class="mt-5">

                        <InputLabel
                            for="password"
                            value="Contraseña"
                        />

                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />

                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />

                    </div>




                    <div class="mt-5 flex items-center">

                        <Checkbox
                            v-model:checked="form.remember"
                            name="remember"
                        />

                        <span class="ml-2 text-sm text-slate-600">
                            Mantener sesión iniciada
                        </span>

                    </div>




                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-7 w-full rounded-lg bg-emerald-600 py-3.5 font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ form.processing
                            ? 'Ingresando...'
                            : 'Iniciar sesión'
                        }}
                    </button>


                    <div class="mt-6 text-center text-xs text-slate-400">
                        Acceso restringido al personal autorizado
                    </div>

                </form>


                <div class="mt-8 text-center text-xs text-slate-400">
                    CEFTE · Sistema de gestión administrativa
                </div>

            </div>

        </div>

    </div>
</template>
