<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Table from '@/Components/UI/Table.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    unidades: Object,
    filters: Object,
})

const buscar = ref(props.filters.buscar ?? '')

watch(buscar, (value) => {
    router.get(
        route('unidades.index'),
        { buscar: value },
        { preserveState: true, replace: true }
    )
})

const headers = [
    'Nombre',
    'Símbolo',
    'Variantes',
    'Acciones'
]

const eliminar = (id) => {
    if (!confirm('¿Está seguro de eliminar esta unidad de medida?')) {
        return
    }
    router.delete(
        route('unidades.destroy', id),
        { preserveScroll: true }
    )
}
</script>

<template>
    <Head title="Unidades de medida" />

    <AppLayout>

        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Unidades de medida
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Catálogo de unidades utilizadas en las variantes
                </p>
            </div>

            <div class="flex items-center gap-3">

                <input
                    v-model="buscar"
                    type="text"
                    placeholder="Buscar unidad..."
                    class="w-64 rounded-lg border-slate-300
                           focus:border-emerald-500
                           focus:ring-emerald-500"
                />

                <Link :href="route('unidades.create')">
                    <Button>
                        Nueva unidad
                    </Button>
                </Link>

            </div>

        </div>

        <Table :headers="headers">

            <tr
                v-for="unidad in unidades.data"
                :key="unidad.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition"
            >

                <td class="p-4 font-medium text-slate-800">
                    {{ unidad.name }}
                </td>

                <td class="p-4 text-slate-600">
                    {{ unidad.symbol }}
                </td>

                <td class="p-4 text-slate-700">
                    {{ unidad.product_variants_count }}
                </td>

                <td class="p-4">
                    <div class="flex gap-2">

                        <Link
                            :href="route('unidades.edit', unidad.id)"
                        >
                            <Button color="info">
                                Editar
                            </Button>
                        </Link>

                        <Button
                            color="danger"
                            @click="eliminar(unidad.id)"
                        >
                            Eliminar
                        </Button>

                    </div>
                </td>

            </tr>

            <tr v-if="unidades.data.length === 0">
                <td
                    :colspan="headers.length"
                    class="p-8 text-center text-slate-500"
                >
                    No existen unidades de medida registradas.
                </td>
            </tr>

        </Table>

        <div
            v-if="unidades.links && unidades.links.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template
                v-for="link in unidades.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
                    class="px-3 py-2 rounded-lg border text-sm transition"
                    :class="link.active
                        ? 'bg-emerald-600 text-white border-emerald-600'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                />
                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-2 rounded-lg border border-slate-200
                           text-sm text-slate-300 bg-slate-50"
                />
            </template>
        </div>

    </AppLayout>
</template>