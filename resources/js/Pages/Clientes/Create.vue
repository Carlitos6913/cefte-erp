<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const form = useForm({
    nombre: '',
    ci_nit: '',
    telefono: '',
    email: '',
    direccion: '',
})


const guardar = () => {
    form.post(route('clientes.store'))
}

</script>


<template>

    <Head title="Nuevo cliente" />

    <AppLayout>

        <div class="max-w-2xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('clientes.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Nuevo cliente
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Registra un nuevo cliente en el sistema.
                </p>

            </div>


            <form
                @submit.prevent="guardar"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Nombre completo *
                    </label>

                    <input
                        v-model="form.nombre"
                        type="text"
                        placeholder="Nombre del cliente"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    />

                    <p
                        v-if="form.errors.nombre"
                        class="text-sm text-red-600 mt-1"
                    >
                        {{ form.errors.nombre }}
                    </p>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            CI / NIT
                        </label>

                        <input
                            v-model="form.ci_nit"
                            type="text"
                            placeholder="Ej. 1234567"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />

                        <p
                            v-if="form.errors.ci_nit"
                            class="text-sm text-red-600 mt-1"
                        >
                            {{ form.errors.ci_nit }}
                        </p>

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Teléfono
                        </label>

                        <input
                            v-model="form.telefono"
                            type="text"
                            placeholder="Ej. 71234567"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="correo@ejemplo.com"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />

                        <p
                            v-if="form.errors.email"
                            class="text-sm text-red-600 mt-1"
                        >
                            {{ form.errors.email }}
                        </p>

                    </div>

                </div>


                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Dirección
                    </label>

                    <textarea
                        v-model="form.direccion"
                        rows="2"
                        placeholder="Dirección del cliente..."
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    ></textarea>

                </div>


                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">

                    <Link :href="route('clientes.index')">
                        <Button color="secondary">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar cliente' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
