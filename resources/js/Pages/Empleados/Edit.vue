<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'


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

        <PageHeader title="Editar empleado" subtitle="Modifica la información del empleado.">
            <template #actions>
                <Link :href="route('empleados.index')">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver al listado
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="max-w-3xl mx-auto">

            <form
                @submit.prevent="actualizar"
                class="space-y-5"
            >

                <FormSection
                    title="Datos personales"
                    description="Información principal para identificar al empleado."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <FormField
                            label="Nombre"
                            forId="empleado-nombre"
                            :error="form.errors.first_name"
                            required
                        >
                            <input
                                id="empleado-nombre"
                                v-model="form.first_name"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.first_name }"
                            />
                        </FormField>

                        <FormField
                            label="Apellido paterno"
                            forId="empleado-apellido-paterno"
                            :error="form.errors.first_surname"
                            required
                        >
                            <input
                                id="empleado-apellido-paterno"
                                v-model="form.first_surname"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.first_surname }"
                            />
                        </FormField>

                        <FormField
                            label="Apellido materno"
                            forId="empleado-apellido-materno"
                            :error="form.errors.second_surname"
                            optional
                        >
                            <input
                                id="empleado-apellido-materno"
                                v-model="form.second_surname"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.second_surname }"
                            />
                        </FormField>

                        <FormField
                            label="CI / NIT"
                            forId="empleado-ci-nit"
                            :error="form.errors.identity_card"
                            hint="Documento de identidad del empleado."
                            required
                        >
                            <input
                                id="empleado-ci-nit"
                                v-model="form.identity_card"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.identity_card }"
                            />
                        </FormField>

                        <FormField
                            label="Profesión / Oficio"
                            forId="empleado-profesion"
                            :error="form.errors.profession"
                            class="md:col-span-2"
                            optional
                        >
                            <input
                                id="empleado-profesion"
                                v-model="form.profession"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.profession }"
                            />
                        </FormField>

                        <FormField
                            label="Dirección"
                            forId="empleado-direccion"
                            :error="form.errors.address"
                            class="md:col-span-2"
                            optional
                        >
                            <input
                                id="empleado-direccion"
                                v-model="form.address"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.address }"
                            />
                        </FormField>

                    </div>
                </FormSection>


                <FormSection
                    title="Contacto"
                    description="Información de contacto del empleado."
                    tone="blue"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <FormField
                            label="Teléfono"
                            forId="empleado-telefono"
                            :error="form.errors.phone"
                            optional
                        >
                            <input
                                id="empleado-telefono"
                                v-model="form.phone"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.phone }"
                            />
                        </FormField>

                        <FormField
                            label="Correo electrónico"
                            forId="empleado-email"
                            :error="form.errors.email"
                            optional
                        >
                            <input
                                id="empleado-email"
                                v-model="form.email"
                                type="email"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.email }"
                            />
                        </FormField>

                    </div>
                </FormSection>


                <FormActions>

                    <Link :href="route('empleados.index')">
                        <Button color="secondary" class="w-full sm:w-auto">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </Button>

                </FormActions>

            </form>

        </div>

    </AppLayout>

</template>
