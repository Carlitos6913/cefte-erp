<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'
import ProductVisual from '@/Components/ProductVisual.vue'

const props = defineProps({
    producto: Object,
    categorias: Array,
})

const form = useForm({
    name: props.producto.name,
    description: props.producto.description ?? '',
    category_id: props.producto.category_id ?? '',
    image: null,
})

const previewImagen = ref(props.producto.image_url ?? null)

const seleccionarImagen = (event) => {

    const archivo = event.target.files?.[0] ?? null

    form.image = archivo

    if (previewImagen.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewImagen.value)
    }

    previewImagen.value = archivo
        ? URL.createObjectURL(archivo)
        : (props.producto.image_url ?? null)

}

const actualizar = () => {

    form.put(
        route('productos.update', props.producto.id),
        {
            preserveScroll: true
        }
    )

}

const categoriaSeleccionada = computed(() => {

    return props.categorias?.find(
        (categoria) => String(categoria.id) === String(form.category_id)
    )?.name ?? ''

})

const nombreProducto = computed(() => form.name?.trim() || 'Producto sin nombre')

</script>


<template>

    <Head title="Editar producto" />

    <AppLayout>

        <div class="max-w-5xl mx-auto">

            <PageHeader title="Editar producto" subtitle="Modifica la información del producto.">
                <template #icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </template>
                <template #actions>
                    <Link :href="route('productos.index')">
                        <Button color="secondary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            Volver al listado
                        </Button>
                    </Link>
                </template>
            </PageHeader>

            <form @submit.prevent="actualizar" class="space-y-5">

                <FormLayout :aside="true">

                    <FormSection
                        title="Identificación"
                        description="Datos que identifican el producto dentro del inventario."
                        tone="emerald"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <FormField
                                label="Código"
                                for-id="producto-code"
                                hint="El código no puede modificarse."
                            >
                                <input
                                    id="producto-code"
                                    :value="producto.code"
                                    type="text"
                                    disabled
                                    readonly
                                    class="form-input font-mono uppercase"
                                />
                            </FormField>

                            <FormField
                                label="Nombre"
                                for-id="producto-name"
                                :error="form.errors.name"
                                hint="Nombre claro y descriptivo del producto."
                                required
                            >
                                <input
                                    id="producto-name"
                                    v-model="form.name"
                                    type="text"
                                    maxlength="150"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.name }"
                                />
                            </FormField>

                        </div>

                    </FormSection>

                    <FormSection
                        title="Clasificación"
                        description="Agrupa el producto para facilitar búsquedas y reportes."
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V6.75A2.25 2.25 0 014.5 4.5h4.5l2.25 2.25h8.25a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25v-5.25z" />
                            </svg>
                        </template>

                        <FormField
                            label="Categoría"
                            for-id="producto-category"
                            :error="form.errors.category_id"
                            hint="Puedes dejar el producto sin categoría."
                            optional
                        >
                            <select
                                id="producto-category"
                                v-model="form.category_id"
                                class="form-select"
                                :class="{ 'is-invalid': form.errors.category_id }"
                            >
                                <option value="">
                                    Sin categoría
                                </option>
                                <option
                                    v-for="categoria in categorias"
                                    :key="categoria.id"
                                    :value="categoria.id"
                                >
                                    {{ categoria.name }}
                                </option>
                            </select>
                        </FormField>

                    </FormSection>

                    <FormSection
                        title="Detalles"
                        description="Información adicional que ayude a identificar el producto."
                        tone="amber"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5A3.375 3.375 0 0010.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </template>

                        <FormField
                            label="Descripción"
                            for-id="producto-description"
                            :error="form.errors.description"
                            hint="Incluye características, presentación o notas útiles."
                            optional
                        >
                            <textarea
                                id="producto-description"
                                v-model="form.description"
                                rows="3"
                                class="form-textarea"
                                :class="{ 'is-invalid': form.errors.description }"
                            ></textarea>
                        </FormField>

                    </FormSection>

                    <FormSection
                        title="Imagen"
                        description="Actualiza la imagen para identificar el producto."
                        tone="violet"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </template>

                        <FormField
                            label="Imagen del producto"
                            for-id="producto-image"
                            :error="form.errors.image"
                            hint="JPG, PNG o WEBP. Tamaño máximo 2 MB."
                            optional
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                                <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 flex items-center justify-center">
                                    <img
                                        v-if="previewImagen"
                                        :src="previewImagen"
                                        alt="Vista previa"
                                        class="h-full w-full object-cover"
                                    />
                                    <svg
                                        v-else
                                        class="w-8 h-8 text-slate-300"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.6"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>

                                <label
                                    for="producto-image"
                                    class="group flex flex-1 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-violet-200 bg-violet-50/30 px-4 py-6 text-center transition hover:border-violet-300 hover:bg-violet-50"
                                >
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 text-violet-600">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V6.75m0 0L8.25 10.5M12 6.75l3.75 3.75M4.5 19.5h15" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-700">
                                        Haz clic para seleccionar una imagen
                                    </span>
                                    <span v-if="previewImagen" class="text-xs text-slate-400">
                                        Selecciona otra imagen para reemplazar la actual.
                                    </span>
                                    <span v-else class="text-xs text-slate-400">
                                        Aún no hay una imagen disponible.
                                    </span>
                                    <input
                                        id="producto-image"
                                        type="file"
                                        accept="image/*"
                                        class="sr-only"
                                        @change="seleccionarImagen"
                                    />
                                </label>

                            </div>
                        </FormField>

                    </FormSection>

                    <template #aside>

                        <FormHelp
                            title="Vista previa"
                            description="Así se verá el producto en el inventario."
                            tone="violet"
                        >
                            <template #icon>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </template>

                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <ProductVisual
                                    :name="nombreProducto"
                                    :category="categoriaSeleccionada"
                                    :image="previewImagen"
                                    class="aspect-square w-full"
                                />
                            </div>

                            <p class="mt-3 truncate text-sm font-semibold text-slate-700">
                                {{ nombreProducto }}
                            </p>
                            <span class="mt-1 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                {{ categoriaSeleccionada || 'Sin categoría' }}
                            </span>
                        </FormHelp>

                        <FormHelp
                            title="Consejos"
                            description="Buenas prácticas al editar productos."
                            tone="emerald"
                        >
                            <template #icon>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                                </svg>
                            </template>

                            <ul class="space-y-3 text-xs text-slate-500">
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-emerald-500 mt-px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>El código del producto no se modifica al editar.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-emerald-500 mt-px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Puedes reemplazar la imagen manteniendo el resto de datos.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-emerald-500 mt-px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Verifica la categoría para conservar los reportes ordenados.</span>
                                </li>
                            </ul>
                        </FormHelp>

                    </template>

                </FormLayout>

                <FormActions>
                    <Link :href="route('productos.index')">
                        <Button type="button" color="secondary" class="w-full sm:w-auto">
                            Cancelar
                        </Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </Button>
                </FormActions>

            </form>

        </div>

    </AppLayout>

</template>
