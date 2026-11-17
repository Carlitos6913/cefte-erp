<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'

const form = useForm({
    code: '',
    name: '',
    description: '',
})

const guardar = () => {
    form.post(route('categorias.store'))
}
</script>

<template>
    <Head title="Nueva categoría" />

    <AppLayout>

        <PageHeader title="Nueva categoría" subtitle="Registra una nueva categoría de productos.">
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                </svg>
            </template>
            <template #actions>
                <Link :href="route('categorias.index')">
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
                @submit.prevent="guardar"
                class="space-y-5"
            >

                <FormSection
                    title="Datos de la categoría"
                    description="Identifica la categoría y describe su propósito."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <FormField
                            label="Código"
                            for-id="categoria-code"
                            :error="form.errors.code"
                            hint="Código corto para identificar la categoría."
                            required
                        >
                            <input
                                id="categoria-code"
                                v-model="form.code"
                                type="text"
                                maxlength="20"
                                class="form-input uppercase"
                                :class="{ 'is-invalid': form.errors.code }"
                                placeholder="Ej. BEB"
                            />
                        </FormField>

                        <FormField
                            label="Nombre"
                            for-id="categoria-name"
                            :error="form.errors.name"
                            hint="Nombre visible en el inventario."
                            required
                        >
                            <input
                                id="categoria-name"
                                v-model="form.name"
                                type="text"
                                maxlength="100"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.name }"
                                placeholder="Ej. Bebidas"
                            />
                        </FormField>

                    </div>

                    <div class="mt-5">
                        <FormField
                            label="Descripción"
                            for-id="categoria-description"
                            :error="form.errors.description"
                            hint="Detalla qué tipo de productos agrupa esta categoría."
                            optional
                        >
                            <textarea
                                id="categoria-description"
                                v-model="form.description"
                                rows="4"
                                class="form-textarea"
                                :class="{ 'is-invalid': form.errors.description }"
                                placeholder="Descripción de la categoría..."
                            ></textarea>
                        </FormField>
                    </div>

                </FormSection>

                <FormActions>
                    <Link :href="route('categorias.index')">
                        <Button color="secondary" class="w-full sm:w-auto">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar categoría' }}
                    </Button>
                </FormActions>

            </form>

        </div>

    </AppLayout>
</template>
