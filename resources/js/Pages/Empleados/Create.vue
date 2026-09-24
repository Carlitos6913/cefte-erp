<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const form = useForm({
    first_name: '',
    first_surname: '',
    second_surname: '',
    identity_card: '',
    phone: '',
    address: '',
    profession: '',
    email: '',
})


const guardar = () => {
    form.post(route('empleados.store'))
}

</script>


<template>

    <Head title="Nuevo empleado" />

    <AppLayout>

        <div class="max-w-2xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('empleados.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Nuevo empleado
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Registra un nuevo empleado en el sistema.
                </p>

            </div>


            <form
                @submit.prevent="guardar"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            placeholder="Nombre del empleado"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                        <p v-if="form.errors.first_name" class="text-sm text-red-600 mt-1">
                            {{ form.errors.first_name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Apellido paterno <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.first_surname"
                            type="text"
                            placeholder="Apellido paterno"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                        <p v-if="form.errors.first_surname" class="text-sm text-red-600 mt-1">
                            {{ form.errors.first_surname }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Apellido materno
                        </label>
                        <input
                            v-model="form.second_surname"
                            type="text"
                            placeholder="Apellido materno"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            CI / NIT <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.identity_card"
                            type="text"
                            placeholder="Ej. 8492011 CB"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                        <p v-if="form.errors.identity_card" class="text-sm text-red-600 mt-1">
                            {{ form.errors.identity_card }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Teléfono
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="Ej. 71234567"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Profesión / Oficio
                        </label>
                        <input
                            v-model="form.profession"
                            type="text"
                            placeholder="Ej. Técnico Superior en Agroindustria"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Dirección
                        </label>
                        <input
                            v-model="form.address"
                            type="text"
                            placeholder="Av. Principal S/N, Eterazama"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Correo electrónico
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="correo@ejemplo.com"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                </div>


                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">

                    <Link :href="route('empleados.index')">
                        <Button color="secondary">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar empleado' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
