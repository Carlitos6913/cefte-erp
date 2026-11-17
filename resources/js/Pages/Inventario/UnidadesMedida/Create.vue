<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'

const form = useForm({
    name: '',
    symbol: '',
})

const guardar = () => {
    form.post(route('unidades.store'))
}
</script>

<template>
    <Head title="Nueva unidad de medida" />

    <AppLayout>

        <PageHeader title="Nueva unidad de medida" subtitle="Registra una nueva unidad de medida.">
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                </svg>
            </template>
            <template #actions>
                <Link :href="route('unidades.index')">
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
                    title="Datos de la unidad"
                    description="Define el nombre y el símbolo con el que se medirán los productos."
                    tone="emerald"
                >
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                        </svg>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <FormField
                            label="Nombre"
                            for-id="unidad-name"
                            :error="form.errors.name"
                            hint="Nombre completo de la unidad, por ejemplo Kilogramo."
                            required
                        >
                            <input
                                id="unidad-name"
                                v-model="form.name"
                                type="text"
                                maxlength="50"
                                class="form-input"
                                :class="{ 'is-invalid': form.errors.name }"
                                placeholder="Ej. Kilogramo"
                            />
                        </FormField>

                        <FormField
                            label="Símbolo"
                            for-id="unidad-symbol"
                            :error="form.errors.symbol"
                            hint="Abreviatura que se mostrará en el inventario."
                            required
                        >
                            <input
                                id="unidad-symbol"
                                v-model="form.symbol"
                                type="text"
                                maxlength="10"
                                class="form-input font-mono"
                                :class="{ 'is-invalid': form.errors.symbol }"
                                placeholder="Ej. kg"
                            />
                        </FormField>

                    </div>

                </FormSection>

                <FormActions>
                    <Link :href="route('unidades.index')">
                        <Button color="secondary" class="w-full sm:w-auto">
                            Cancelar
                        </Button>
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar unidad' }}
                    </Button>
                </FormActions>

            </form>

        </div>

    </AppLayout>
</template>
