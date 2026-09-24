<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'

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

        <div class="max-w-2xl">

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800">
                    Nueva categoría
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Registra una nueva categoría de productos.
                </p>
            </div>

            <form
                @submit.prevent="guardar"
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
                        placeholder="Ej. BEB"
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
                        placeholder="Ej. Bebidas"
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
                        placeholder="Descripción de la categoría..."
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
                        {{ form.processing ? 'Guardando...' : 'Guardar categoría' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>
</template>