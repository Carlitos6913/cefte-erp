<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'

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

        <div class="max-w-2xl">

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800">
                    Nueva unidad de medida
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Registra una nueva unidad de medida.
                </p>
            </div>

            <form
                @submit.prevent="guardar"
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
                        placeholder="Ej. Kilogramo"
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
                        placeholder="Ej. kg"
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
                        {{ form.processing ? 'Guardando...' : 'Guardar unidad' }}
                    </Button>

                </div>

            </form>

        </div>

    </AppLayout>
</template>