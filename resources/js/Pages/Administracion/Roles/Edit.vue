<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import Button from '@/Components/UI/Button.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'

const props = defineProps({
    role: Object,
    modulos: Array,
    modulosDisponibles: Object,
})

const form = useForm({
    name: props.role.name,
    modulos: [...(props.modulos ?? [])],
})

const accesoTotal = computed(() => form.modulos.length === 0)

const totalModulos = computed(() => Object.keys(props.modulosDisponibles ?? {}).length)

const seleccionarTodos = () => {
    form.modulos = Object.keys(props.modulosDisponibles ?? {})
}

const limpiarSeleccion = () => {
    form.modulos = []
}

const guardar = () => {
    form.put(route('roles.update', props.role.id))
}

</script>

<template>

    <Head :title="'Editar rol · ' + role.name" />

    <AppLayout>

        <PageHeader
            title="Editar rol"
            :subtitle="'Configura el acceso de ' + role.name + ' a los módulos del sistema'"
        >
            <template #icon>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </template>

            <template #actions>
                <Link :href="route('roles.index')">
                    <Button color="secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver a roles
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="max-w-5xl mx-auto">

            <FormLayout aside>

                <form @submit.prevent="guardar" class="space-y-5">

                    <FormSection
                        title="Información del rol"
                        description="Nombre con el que se identifica el perfil."
                        tone="emerald"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        </template>

                        <FormField
                            label="Nombre del rol"
                            forId="rol-nombre"
                            :error="form.errors.name"
                            hint="Usa un nombre claro y breve para identificar el perfil."
                            required
                        >
                            <input
                                id="rol-nombre"
                                v-model="form.name"
                                type="text"
                                placeholder="Nombre del rol"
                                class="form-input max-w-md"
                                :class="{ 'is-invalid': form.errors.name }"
                            />
                        </FormField>
                    </FormSection>

                    <FormSection
                        title="Acceso a módulos"
                        :description="form.modulos.length + ' de ' + totalModulos + ' módulos seleccionados'"
                        tone="blue"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </template>

                        <template #actions>
                            <div class="flex items-center gap-2">
                                <Button type="button" color="secondary" size="sm" @click="seleccionarTodos">
                                    Seleccionar todos
                                </Button>
                                <Button type="button" color="ghost" size="sm" @click="limpiarSeleccion">
                                    Limpiar
                                </Button>
                            </div>
                        </template>

                        <div class="space-y-4">

                            <div
                                v-if="accesoTotal"
                                class="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-100"
                            >
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-emerald-700">Acceso total</p>
                                    <p class="text-xs text-emerald-600/80 mt-0.5">
                                        Al no seleccionar módulos, este rol podrá acceder a todos los módulos del sistema.
                                    </p>
                                </div>
                            </div>

                            <FormField
                                label="Módulos del sistema"
                                :error="form.errors.modulos"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <label
                                        v-for="(nombre, clave) in modulosDisponibles"
                                        :key="clave"
                                        class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                        :class="form.modulos.includes(clave)
                                            ? 'border-emerald-200 bg-emerald-50/60'
                                            : 'border-slate-200 bg-white hover:bg-slate-50'"
                                    >
                                        <input
                                            :id="'modulo-' + clave"
                                            v-model="form.modulos"
                                            :value="clave"
                                            type="checkbox"
                                            class="form-checkbox w-4 h-4"
                                        />
                                        <span class="text-sm font-medium text-slate-700">{{ nombre }}</span>
                                    </label>
                                </div>
                            </FormField>

                        </div>
                    </FormSection>

                    <FormActions>
                        <Link :href="route('roles.index')">
                            <Button color="secondary" class="w-full sm:w-auto">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </FormActions>

                </form>

                <template #aside>

                    <FormHelp
                        title="Acceso total"
                        description="Cómo interpretar la selección de módulos."
                        tone="emerald"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>

                        <p>
                            Si no seleccionas ningún módulo, el rol tendrá acceso total al sistema.
                            Para restringir el acceso, selecciona al menos un módulo de la lista.
                        </p>

                        <div
                            class="mt-4 rounded-xl border p-3"
                            :class="accesoTotal
                                ? 'border-emerald-100 bg-emerald-50'
                                : 'border-blue-100 bg-blue-50'"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                Estado actual
                            </p>
                            <p
                                class="text-sm font-semibold mt-0.5"
                                :class="accesoTotal ? 'text-emerald-700' : 'text-blue-700'"
                            >
                                {{ accesoTotal ? 'Acceso total' : form.modulos.length + ' de ' + totalModulos + ' módulos' }}
                            </p>
                        </div>
                    </FormHelp>

                    <FormHelp
                        title="Recomendaciones"
                        description="Mantén los permisos bajo control."
                        tone="slate"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                            </svg>
                        </template>

                        <ul class="space-y-2.5">
                            <li class="flex gap-2">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                <span>Selecciona solo los módulos que el rol necesita.</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                <span>Usa Seleccionar todos únicamente para perfiles administrativos.</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                <span>Limpiar la selección otorga acceso total al rol.</span>
                            </li>
                        </ul>
                    </FormHelp>

                </template>

            </FormLayout>

        </div>

    </AppLayout>

</template>
