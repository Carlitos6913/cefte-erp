<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    empleado: Object,
})


const form = useForm({
    first_name: props.empleado.first_name,
    first_surname: props.empleado.first_surname,
    second_surname: props.empleado.second_surname ?? '',
    identity_card: props.empleado.identity_card,
    phone: props.empleado.phone ?? '',
    address: props.empleado.address ?? '',
    profession: props.empleado.profession ?? '',
    email: props.empleado.email ?? '',
})


const actualizar = () => {
    form.put(route('empleados.update', props.empleado.id))
}

</script>


<template>

    <Head title="Editar empleado" />

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
                        Editar empleado
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Modifica la información del empleado.
                </p>

            </div>


            <form
                @submit.prevent="actualizar"
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
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
