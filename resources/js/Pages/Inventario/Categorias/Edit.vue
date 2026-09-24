<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    categoria: Object,
})

const form = useForm({
    code: props.categoria.code,
    name: props.categoria.name,
    description: props.categoria.description || '',
})

const actualizar = () => {
    form.put(route('categorias.update', props.categoria.id))
}
</script>

<template>
    <Head title="Editar categoría" />

    <AppLayout>

        <div class="max-w-2xl">

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800">
                    Editar categoría
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Modifica la información de la categoría.
                </p>
            </div>

            <form
                @submit.prevent="actualizar"
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6"
            >

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Código
                    </label>

                    <input
                        v-model="form.code"
                        type="text"
                        maxlength="20"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    />

                    <p
                        v-if="form.errors.code"
                        class="text-sm text-red-500 mt-1"
                    >
                        {{ form.errors.code }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nombre
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
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
                        Descripción
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    ></textarea>

                    <p
                        v-if="form.errors.description"
                        class="text-sm text-red-500 mt-1"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="flex justify-end gap-3">

                    <Link :href="route('categorias.index')">
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