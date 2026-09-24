<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    unidad: Object,
})

const form = useForm({
    name: props.unidad.name,
    symbol: props.unidad.symbol,
})

const actualizar = () => {
    form.put(route('unidades.update', props.unidad.id))
}
</script>

<template>
    <Head title="Editar unidad de medida" />

    <AppLayout>

        <div class="max-w-2xl">

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800">
                    Editar unidad de medida
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Modifica la información de la unidad de medida.
                </p>
            </div>

            <form
                @submit.prevent="actualizar"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nombre
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        maxlength="50"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    />

                    <p
                        v-if="form.errors.name"
                        class="text-sm text-red-500 mt-1"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Símbolo
                    </label>

                    <input
                        v-model="form.symbol"
                        type="text"
                        maxlength="10"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    />

                    <p
                        v-if="form.errors.symbol"
                        class="text-sm text-red-500 mt-1"
                    >
                        {{ form.errors.symbol }}
                    </p>
                </div>

                <div class="flex justify-end gap-3">

                    <Link :href="route('unidades.index')">
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