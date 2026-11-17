<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'


const props = defineProps({
    variante: Object,
    productos: Array,
    unidades: Array,
    unidadesBase: Array,
    unidadesEmpaque: Array,
})


const form = useForm({

    product_id: props.variante.product_id,
    unit_measure_id: props.variante.unit_measure_id,
    unidad_base_id: props.variante.unidad_base_id ?? '',
    unidad_empaque_id: props.variante.unidad_empaque_id ?? '',
    factor_empaque: props.variante.factor_empaque ?? '',
    flavor: props.variante.flavor ?? '',
    packaging_type: props.variante.packaging_type ?? '',
    net_content: props.variante.net_content ?? '',
    minimum_stock: props.variante.minimum_stock,
    unit_price: props.variante.unit_price,

})


/**
 * Si la variante declara empaque, la caja solo aparece cuando el
 * factor indica cuántas unidades base trae.
 */
const usaEmpaque = computed(() => {

    return form.unidad_empaque_id !== ''
        && form.unidad_empaque_id !== null
        && Number(form.factor_empaque) > 0

})


const unidadesBaseDisponibles = computed(() => {
    return props.unidadesBase?.length
        ? props.unidadesBase
        : props.unidades
})


const productoSeleccionado = computed(() => {
    return props.productos.find((p) => p.id === form.product_id)
})


const unidadBaseSeleccionada = computed(() => {
    return unidadesBaseDisponibles.value.find((u) => u.id == form.unidad_base_id)
})


const unidadEmpaqueSeleccionada = computed(() => {
    return (props.unidadesEmpaque ?? []).find((u) => u.id == form.unidad_empaque_id)
})


const hintFactor = computed(() => {

    if (usaEmpaque.value) {
        const empaque = unidadEmpaqueSeleccionada.value?.name ?? 'empaque'

        return `1 ${empaque} = ${Number(form.factor_empaque)} unidades. El ingreso y la venta podrán capturarse por caja o por unidad.`
    }

    return 'Déjalo vacío si el producto se maneja solo por unidades.'

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

        <div class="max-w-6xl mx-auto">

            <PageHeader title="Editar variante" subtitle="Modifica la información de la variante.">
                <template #actions>
                    <Link :href="route('variantes.index')">
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

                <FormLayout aside>

                    <FormSection
                        title="Producto y presentación"
                        description="Selecciona el producto y define su presentación comercial."
                        tone="emerald"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0v9l-8.25 4.5m8.25-13.5l-8.25 4.5m0 9l-8.25-4.5v-9m8.25 13.5v-9M3.75 7.5l8.25 4.5" />
                            </svg>
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div class="md:col-span-2">
                                <FormField
                                    label="Producto"
                                    for-id="product_id"
                                    :error="form.errors.product_id"
                                    required
                                >
                                    <select
                                        id="product_id"
                                        v-model="form.product_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.product_id }"
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
                                </FormField>
                            </div>

                            <FormField
                                label="SKU"
                                for-id="sku_variante"
                                hint="El SKU no puede modificarse."
                            >
                                <input
                                    id="sku_variante"
                                    :value="variante.sku"
                                    type="text"
                                    disabled
                                    readonly
                                    class="form-input font-mono"
                                />
                            </FormField>

                            <FormField
                                label="Unidad de medida"
                                for-id="unit_measure_id"
                                :error="form.errors.unit_measure_id"
                                required
                            >
                                <select
                                    id="unit_measure_id"
                                    v-model="form.unit_measure_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.unit_measure_id }"
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
                            </FormField>

                            <FormField
                                label="Sabor"
                                for-id="flavor"
                                :error="form.errors.flavor"
                                optional
                            >
                                <input
                                    id="flavor"
                                    v-model="form.flavor"
                                    type="text"
                                    maxlength="100"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.flavor }"
                                />
                            </FormField>

                            <FormField
                                label="Tipo de empaque"
                                for-id="packaging_type"
                                :error="form.errors.packaging_type"
                                optional
                            >
                                <input
                                    id="packaging_type"
                                    v-model="form.packaging_type"
                                    type="text"
                                    maxlength="100"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.packaging_type }"
                                />
                            </FormField>

                            <div class="md:col-span-2">
                                <FormField
                                    label="Contenido neto"
                                    for-id="net_content"
                                    :error="form.errors.net_content"
                                    optional
                                >
                                    <input
                                        id="net_content"
                                        v-model="form.net_content"
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="form-input text-right"
                                        :class="{ 'is-invalid': form.errors.net_content }"
                                    />
                                </FormField>
                            </div>

                        </div>
                    </FormSection>

                    <FormSection
                        title="Unidades y empaque"
                        description="Define cómo se controla el stock y el empaque de almacén."
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m11.142-4.5L21.75 12l-4.179 2.25M6.429 9.75L12 6l5.571 3.75M6.429 9.75v4.5L12 18l5.571-3.75v-4.5M12 18v3.75" />
                            </svg>
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <FormField
                                label="Unidad de stock"
                                for-id="unidad_base_id"
                                :error="form.errors.unidad_base_id"
                                hint="Unidad en la que se controla y se vende el stock (una botella, una lata, una bolsa)."
                                required
                            >
                                <select
                                    id="unidad_base_id"
                                    v-model="form.unidad_base_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.unidad_base_id }"
                                >
                                    <option value="">
                                        Seleccionar unidad
                                    </option>
                                    <option
                                        v-for="unidad in unidadesBaseDisponibles"
                                        :key="unidad.id"
                                        :value="unidad.id"
                                    >
                                        {{ unidad.name }} ({{ unidad.symbol }})
                                    </option>
                                </select>
                            </FormField>

                            <FormField
                                label="Empaque de almacén"
                                for-id="unidad_empaque_id"
                                :error="form.errors.unidad_empaque_id"
                                optional
                            >
                                <select
                                    id="unidad_empaque_id"
                                    v-model="form.unidad_empaque_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.unidad_empaque_id }"
                                >
                                    <option value="">
                                        Sin empaque
                                    </option>
                                    <option
                                        v-for="unidad in unidadesEmpaque"
                                        :key="unidad.id"
                                        :value="unidad.id"
                                    >
                                        {{ unidad.name }} ({{ unidad.symbol }})
                                    </option>
                                </select>
                            </FormField>

                            <div class="md:col-span-2">
                                <FormField
                                    label="Unidades por empaque"
                                    for-id="factor_empaque"
                                    :error="form.errors.factor_empaque"
                                    :hint="hintFactor"
                                    :required="Boolean(form.unidad_empaque_id)"
                                    :optional="! form.unidad_empaque_id"
                                >
                                    <input
                                        id="factor_empaque"
                                        v-model.number="form.factor_empaque"
                                        type="number"
                                        min="1"
                                        step="1"
                                        placeholder="Ej: 24"
                                        :disabled="!form.unidad_empaque_id"
                                        class="form-input text-right"
                                        :class="{ 'is-invalid': form.errors.factor_empaque }"
                                    />
                                </FormField>
                            </div>

                        </div>
                    </FormSection>

                    <FormSection
                        title="Precios y stock"
                        description="Define el precio de venta y el stock mínimo para alertas."
                        tone="amber"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <FormField
                                label="Precio unitario (Bs.)"
                                for-id="unit_price"
                                :error="form.errors.unit_price"
                                required
                            >
                                <input
                                    id="unit_price"
                                    v-model="form.unit_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-input text-right font-mono"
                                    :class="{ 'is-invalid': form.errors.unit_price }"
                                />
                            </FormField>

                            <FormField
                                label="Stock mínimo"
                                for-id="minimum_stock"
                                :error="form.errors.minimum_stock"
                                optional
                            >
                                <input
                                    id="minimum_stock"
                                    v-model="form.minimum_stock"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="form-input text-right font-mono"
                                    :class="{ 'is-invalid': form.errors.minimum_stock }"
                                />
                            </FormField>

                        </div>
                    </FormSection>

                    <template #aside>

                        <FormHelp
                            title="Resumen de la variante"
                            description="Vista previa de la configuración."
                            tone="emerald"
                        >
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2.25-9.75V19.5a2.25 2.25 0 01-2.25 2.25H9a2.25 2.25 0 01-2.25-2.25V4.5A2.25 2.25 0 019 2.25h4.5A2.25 2.25 0 0115.75 4.5z" />
                                </svg>
                            </template>

                            <dl class="space-y-2.5">
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">Producto</dt>
                                    <dd class="text-right font-semibold text-slate-700">
                                        {{ productoSeleccionado ? `${productoSeleccionado.code} - ${productoSeleccionado.name}` : 'Sin seleccionar' }}
                                    </dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">SKU</dt>
                                    <dd class="text-right font-semibold text-slate-700">{{ variante.sku }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">Unidad base</dt>
                                    <dd class="text-right font-semibold text-slate-700">
                                        {{ unidadBaseSeleccionada ? `${unidadBaseSeleccionada.name} (${unidadBaseSeleccionada.symbol})` : 'Sin definir' }}
                                    </dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">Empaque</dt>
                                    <dd class="text-right font-semibold text-slate-700">
                                        {{ usaEmpaque ? `${Number(form.factor_empaque)} x ${unidadEmpaqueSeleccionada?.symbol ?? ''}` : 'Sin empaque' }}
                                    </dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">Precio</dt>
                                    <dd class="text-right font-semibold text-slate-700">Bs. {{ Number(form.unit_price || 0).toFixed(2) }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-slate-400">Stock mínimo</dt>
                                    <dd class="text-right font-semibold text-slate-700">{{ Number(form.minimum_stock || 0) }}</dd>
                                </div>
                            </dl>
                        </FormHelp>

                        <FormHelp
                            title="Unidad base"
                            description="La referencia de todo el inventario."
                            tone="blue"
                        >
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </template>

                            <p>
                                Elige la unidad en la que se cuenta el stock: una botella, una lata o una bolsa.
                                Los ingresos y las salidas se convertirán siempre a esta unidad.
                            </p>
                        </FormHelp>

                        <FormHelp
                            title="Empaque y precios"
                            description="Facilita la compra y la venta."
                            tone="amber"
                        >
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-4.5-9.75h7.125a2.625 2.625 0 010 5.25H9.375a2.625 2.625 0 000 5.25H16.5" />
                                </svg>
                            </template>

                            <p class="mb-2">
                                Si configuras una caja, indica cuántas unidades base contiene.
                                Así podrás registrar ingresos y ventas por caja o por unidad.
                            </p>
                            <p>
                                El precio unitario corresponde siempre a la unidad base del inventario.
                            </p>
                        </FormHelp>

                    </template>

                </FormLayout>

                <FormActions>

                    <Link :href="route('variantes.index')">
                        <Button type="button" color="secondary">
                            Cancelar
                        </Button>
                    </Link>

                    <Button type="submit" :disabled="form.processing">
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </Button>

                </FormActions>

            </form>

        </div>

    </AppLayout>

</template>
