<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


const props = defineProps({
    variante: Object,
    productos: Array,
    unidades: Array,
})


const form = useForm({

    product_id: props.variante.product_id,
    unit_measure_id: props.variante.unit_measure_id,
    sku: props.variante.sku,
    flavor: props.variante.flavor ?? '',
    packaging_type: props.variante.packaging_type ?? '',
    net_content: props.variante.net_content ?? '',
    stock: props.variante.stock,
    minimum_stock: props.variante.minimum_stock,
    unit_price: props.variante.unit_price,

})


const actualizar = () => {

    form.put(
        route('variantes.update', props.variante.id),
        {
            preserveScroll: true
        }
    )

}

</script>


<template>

    <Head title="Editar variante" />

    <AppLayout>

        <div class="max-w-4xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('variantes.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        ←
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Editar variante
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Modifica la información de la variante.
                </p>

            </div>


            <form
                @submit.prevent="actualizar"
                class="bg-white rounded-xl border border-slate-200 shadow-sm"
            >

                <div class="p-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Producto -->

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Producto
                            </label>

                            <select
                                v-model="form.product_id"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            >
                                <option value="">
                                    Seleccionar producto
                                </option>

                                <option
                                    v-for="producto in productos"
                                    :key="producto.id"
                                    :value="producto.id"
                                >
                                    {{ producto.code }} - {{ producto.name }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.product_id"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.product_id }}
                            </p>

                        </div>


                        <!-- SKU -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                SKU
                            </label>

                            <input
                                v-model="form.sku"
                                type="text"
                                maxlength="50"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.sku"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.sku }}
                            </p>

                        </div>


                        <!-- Unidad de medida -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Unidad de medida
                            </label>

                            <select
                                v-model="form.unit_measure_id"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            >
                                <option value="">
                                    Seleccionar unidad
                                </option>

                                <option
                                    v-for="unidad in unidades"
                                    :key="unidad.id"
                                    :value="unidad.id"
                                >
                                    {{ unidad.name }} ({{ unidad.symbol }})
                                </option>
                            </select>

                            <p
                                v-if="form.errors.unit_measure_id"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.unit_measure_id }}
                            </p>

                        </div>


                        <!-- Sabor -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Sabor
                            </label>

                            <input
                                v-model="form.flavor"
                                type="text"
                                maxlength="100"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.flavor"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.flavor }}
                            </p>

                        </div>


                        <!-- Tipo de empaque -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Tipo de empaque
                            </label>

                            <input
                                v-model="form.packaging_type"
                                type="text"
                                maxlength="100"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.packaging_type"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.packaging_type }}
                            </p>

                        </div>


                        <!-- Contenido neto -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Contenido neto
                            </label>

                            <input
                                v-model="form.net_content"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.net_content"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.net_content }}
                            </p>

                        </div>


                        <!-- Precio unitario -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Precio unitario (Bs.)
                            </label>

                            <input
                                v-model="form.unit_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.unit_price"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.unit_price }}
                            </p>

                        </div>


                        <!-- Stock -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Stock actual
                            </label>

                            <input
                                v-model="form.stock"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p class="text-xs text-slate-500 mt-1">
                                El stock también se modifica mediante ingresos, salidas y ventas.
                            </p>

                            <p
                                v-if="form.errors.stock"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.stock }}
                            </p>

                        </div>


                        <!-- Stock mínimo -->

                        <div>

                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Stock mínimo
                            </label>

                            <input
                                v-model="form.minimum_stock"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border-slate-300
                                       focus:border-emerald-500
                                       focus:ring-emerald-500"
                            />

                            <p
                                v-if="form.errors.minimum_stock"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.minimum_stock }}
                            </p>

                        </div>

                    </div>

                </div>


                <div
                    class="flex justify-end gap-3 px-6 py-4
                           bg-slate-50 border-t border-slate-200 rounded-b-xl"
                >

                    <Link :href="route('variantes.index')">

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
                            : 'Guardar cambios'
                        }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>

</template>