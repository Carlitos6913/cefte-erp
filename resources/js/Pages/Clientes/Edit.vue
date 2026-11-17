<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'


const props = defineProps({
    cliente: Object,
})


const form = useForm({
    nombre: props.cliente.nombre,
    ci_nit: props.cliente.ci_nit ?? '',
    telefono: props.cliente.telefono ?? '',
    email: props.cliente.email ?? '',
    direccion: props.cliente.direccion ?? '',
})


const actualizar = () => {
    form.put(route('clientes.update', props.cliente.id))
}

</script>


<template>

    <Head title="Editar cliente" />

    <AppLayout>

        <PageHeader title="Editar cliente" subtitle="Modifica la información del cliente.">
            <template #actions>
                <Link :href="route('clientes.index')">
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
                    title="Datos del cliente"
                    description="Información general para identificar al cliente en el sistema."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </template>

                    <div class="space-y-5">

                        <FormField
                            label="Nombre completo"
                            forId="cliente-nombre"
                            :error="form.errors.nombre"
                            hint="Nombre o razón social con el que se registra el cliente."
                            required
                        >
                            <input
                                id="cliente-nombre"
                                v-model="form.nombre"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.nombre }"
                            />
                        </FormField>

                        <FormField
                            label="CI / NIT"
                            forId="cliente-ci-nit"
                            :error="form.errors.ci_nit"
                            hint="Documento de identidad o identificación tributaria."
                            optional
                        >
                            <input
                                id="cliente-ci-nit"
                                v-model="form.ci_nit"
                                type="text"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.ci_nit }"
                            />
                        </FormField>

                    </div>
                </FormSection>


                <FormSection
                    title="Contacto"
                    description="Canales de comunicación y ubicación del cliente."
                    tone="blue"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                    </template>

                    <div class="space-y-5">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <FormField
                                label="Teléfono"
                                forId="cliente-telefono"
                                :error="form.errors.telefono"
                                optional
                            >
                                <input
                                    id="cliente-telefono"
                                    v-model="form.telefono"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.telefono }"
                                />
                            </FormField>

                            <FormField
                                label="Email"
                                forId="cliente-email"
                                :error="form.errors.email"
                                optional
                            >
                                <input
                                    id="cliente-email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.email }"
                                />
                            </FormField>

                            <FormField
                                label="Dirección"
                                forId="cliente-direccion"
                                :error="form.errors.direccion"
                                class="md:col-span-2"
                                optional
                            >
                                <textarea
                                    id="cliente-direccion"
                                    v-model="form.direccion"
                                    rows="2"
                                    class="form-textarea"
                                    :class="{ 'is-invalid': form.errors.direccion }"
                                ></textarea>
                            </FormField>

                        </div>

                    </div>
                </FormSection>


                <FormActions>

                    <Link :href="route('clientes.index')">
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
