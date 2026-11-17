<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormLayout from '@/Components/UI/FormLayout.vue'
import FormSection from '@/Components/UI/FormSection.vue'
import FormField from '@/Components/UI/FormField.vue'
import FormHelp from '@/Components/UI/FormHelp.vue'
import FormActions from '@/Components/UI/FormActions.vue'


const props = defineProps({
    venta: Object,
})


const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-BO')
}

const formatoMoneda = (valor) => {
    return Number(valor).toFixed(2)
}


const totalPagado = computed(() => {
    if (!props.venta.pagos) return 0
    return props.venta.pagos.reduce(
        (sum, pago) => sum + Number(pago.monto),
        0
    )
})

const saldoRestante = computed(() => {
    return Number(props.venta.saldo_pendiente)
})


const form = useForm({
    venta_id: props.venta.id,
    fecha: new Date().toISOString().split('T')[0],
    monto: props.venta.saldo_pendiente,
    metodo: 'efectivo',
    observaciones: '',
})


const pagarSaldoCompleto = () => {
    form.monto = props.venta.saldo_pendiente
}


const registrar = () => {
    form.post(
        route('pagos.store'),
        { preserveScroll: true }
    )
}

</script>


<template>

    <Head title="Registrar pago" />

    <AppLayout>

        <div class="max-w-6xl mx-auto">

            <PageHeader title="Registrar pago" :subtitle="`Venta ${venta.numero}`">
                <template #icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </template>
                <template #actions>
                    <Link :href="route('pagos.index')">
                        <Button color="secondary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            Volver al listado
                        </Button>
                    </Link>
                </template>
            </PageHeader>


            <form @submit.prevent="registrar">

                <FormLayout :aside="true">

                    <FormSection
                        title="Datos del pago"
                        description="Registra el abono al crédito de la venta seleccionada."
                        tone="amber"
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <FormField label="Fecha del pago" for-id="fecha" :error="form.errors.fecha" required>
                                <input
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    class="form-input"
                                    :class="{ 'is-invalid': form.errors.fecha }"
                                />
                            </FormField>

                            <FormField label="Monto a pagar (Bs.)" for-id="monto" :error="form.errors.monto" required>
                                <input
                                    id="monto"
                                    v-model.number="form.monto"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    :max="saldoRestante"
                                    class="form-input text-right font-mono"
                                    :class="{ 'is-invalid': form.errors.monto }"
                                />
                                <p class="text-xs text-slate-500 mt-1.5">
                                    Saldo restante después del pago:
                                    <span class="font-semibold text-slate-700">
                                        Bs. {{ formatoMoneda(Math.max(0, saldoRestante - form.monto)) }}
                                    </span>
                                </p>
                            </FormField>

                            <div class="md:col-span-2">
                                <FormField label="Método de pago" for-id="metodo" :error="form.errors.metodo" required>
                                    <select
                                        id="metodo"
                                        v-model="form.metodo"
                                        class="form-select md:w-1/2"
                                        :class="{ 'is-invalid': form.errors.metodo }"
                                    >
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="qr">QR</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </FormField>
                            </div>

                            <div class="md:col-span-2">
                                <FormField label="Observaciones" for-id="observaciones" :error="form.errors.observaciones" optional>
                                    <textarea
                                        id="observaciones"
                                        v-model="form.observaciones"
                                        rows="2"
                                        placeholder="Nota sobre el pago..."
                                        class="form-textarea"
                                        :class="{ 'is-invalid': form.errors.observaciones }"
                                    ></textarea>
                                </FormField>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <Button
                                color="secondary"
                                type="button"
                                @click="pagarSaldoCompleto"
                            >
                                Pagar saldo completo (Bs. {{ formatoMoneda(saldoRestante) }})
                            </Button>
                        </div>
                    </FormSection>

                    <FormActions>
                        <Link :href="route('pagos.index')">
                            <Button type="button" color="secondary">Cancelar</Button>
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing || form.monto <= 0 || form.monto > saldoRestante"
                        >
                            {{ form.processing ? 'Registrando...' : 'Registrar pago' }}
                        </Button>
                    </FormActions>

                    <template #aside>
                        <FormHelp title="Resumen de la venta" description="Estado de cuenta" tone="violet">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </template>
                            <dl class="space-y-2.5">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Total venta</dt>
                                    <dd class="text-sm font-semibold text-slate-700">Bs. {{ formatoMoneda(venta.total) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Ya pagado</dt>
                                    <dd class="text-sm font-semibold text-emerald-600">Bs. {{ formatoMoneda(totalPagado) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs text-slate-500">Saldo pendiente</dt>
                                    <dd class="text-sm font-bold text-red-600">Bs. {{ formatoMoneda(saldoRestante) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-100">
                                    <dt class="text-xs text-slate-500">Cliente</dt>
                                    <dd class="text-sm font-semibold text-slate-700 text-right">
                                        {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                                    </dd>
                                </div>
                            </dl>
                        </FormHelp>

                        <FormHelp title="Monto máximo" description="No superes el saldo" tone="amber">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM10.34 3.94l-7.5 12.99A1.5 1.5 0 004.14 19.5h15.72a1.5 1.5 0 001.3-2.57l-7.5-12.99a1.5 1.5 0 00-2.6 0z" />
                                </svg>
                            </template>
                            <ul class="space-y-2 text-xs leading-relaxed">
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>Puedes registrar abonos parciales hasta el saldo pendiente.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>El monto máximo permitido para este pago es Bs. {{ formatoMoneda(saldoRestante) }}.</span>
                                </li>
                            </ul>
                        </FormHelp>

                        <FormHelp title="Buenas prácticas" description="Registro confiable" tone="emerald">
                            <template #icon>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <p class="text-xs leading-relaxed">
                                Confirma la fecha, el método de pago y el monto antes de registrar. Los abonos actualizan el saldo de la venta de forma inmediata.
                            </p>
                        </FormHelp>
                    </template>

                </FormLayout>

            </form>

        </div>

    </AppLayout>

</template>
