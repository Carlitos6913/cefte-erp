<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const form = useForm({

    code: '',
    name: '',
    description: '',
    category_id: '',

})


const guardar = () => {

    form.post(
        route('productos.store'),
        {
            preserveScroll: true
        }
    )

}
const props = defineProps({
    categorias: Array,
})

</script>


<template>

    <Head title="Nuevo producto" />

    <AppLayout>

        <div class="max-w-4xl mx-auto">

            <!-- Encabezado -->

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('productos.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Nuevo producto
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Registra un nuevo producto en el inventario.
                </p>

            </div>


            <!-- Formulario -->

            <form
                @submit.prevent="guardar"
                class="bg-white rounded-xl border border-slate-200 shadow-sm"
            >

                <div class="p-6 space-y-6">


                    <!-- Información básica -->

                    <div>

                        <h2 class="text-lg font-semibold text-slate-800">
                            Información del producto
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            Datos generales del producto.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <!-- Código -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Código
                            </label>

                            <input
                                v-model="form.code"
                                type="text"
                                maxlength="30"
                                placeholder="Ej. PROD-001"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.code"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.code }}
                            </p>

                        </div>

                        <div>
    <label class="block text-sm font-medium text-slate-700 mb-2">
        Categoría
    </label>

    <select
        v-model="form.category_id"
        class="w-full rounded-lg border-slate-300
               focus:border-emerald-500
               focus:ring-emerald-500"
    >
        <option value="">
            Seleccionar categoría
        </option>

        <option
            v-for="categoria in categorias"
            :key="categoria.id"
            :value="categoria.id"
        >
            {{ categoria.name }}
        </option>
    </select>

    <p
        v-if="form.errors.category_id"
        class="text-sm text-red-500 mt-1"
    >
        {{ form.errors.category_id }}
    </p>
</div>


                        <!-- Nombre -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Nombre
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                maxlength="150"
                                placeholder="Nombre del producto"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.name"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.name }}
                            </p>

                        </div>


                        <!-- Descripción -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Descripción
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Descripción opcional..."
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            ></textarea>

                            <p
                                v-if="form.errors.description"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.description }}
                            </p>

                        </div>

                    </div>

                    </div>


                <!-- Botones -->

                <div
                    class="flex justify-end gap-3 px-6 py-4
                           bg-slate-50 border-t border-slate-200 rounded-b-xl"
                >

                    <Link :href="route('productos.index')">

                        <Button
                            type="button"
                            color="secondary"
                        >
                            Cancelar
                        </Button>

                    </Link>


                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing
                            ? 'Guardando...'
                            : 'Guardar producto'
                        }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>