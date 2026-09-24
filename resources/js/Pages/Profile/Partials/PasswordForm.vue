<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const expandir = ref(false)

const actualizar = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            expandir.value = false
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation')
                passwordInput.value?.focus()
            }
            if (form.errors.current_password) {
                form.reset('current_password')
                currentPasswordInput.value?.focus()
            }
        },
    })
}
</script>

<template>
    <div>
        <button
            v-if="!expandir"
            type="button"
            @click="expandir = true"
            class="w-full flex items-center gap-3 p-3 rounded-xl border border-dashed border-slate-200 text-sm text-slate-500 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50/50 transition"
        >
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            Cambiar contraseña
        </button>

        <Transition
            enter-active-class="transition-all ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <form v-if="expandir" @submit.prevent="actualizar" class="space-y-4">

                <button
                    type="button"
                    @click="expandir = false; form.reset(); form.clearErrors()"
                    class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Cancelar
                </button>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Contraseña actual *</label>
                    <input
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                        :class="form.errors.current_password ? 'border-red-300 bg-red-50/50' : ''"
                    />
                    <p v-if="form.errors.current_password" class="text-xs text-red-500 mt-1">{{ form.errors.current_password }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nueva contraseña *</label>
                    <input
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                        :class="form.errors.password ? 'border-red-300 bg-red-50/50' : ''"
                    />
                    <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Confirmar *</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                    />
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-900 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Actualizando...' : 'Actualizar' }}
                    </button>
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Listo
                        </span>
                    </Transition>
                </div>

            </form>
        </Transition>
    </div>
</template>
