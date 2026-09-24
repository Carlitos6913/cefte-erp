<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'


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

        <div class="max-w-3xl mx-auto">

            <div class="mb-6">

                <div class="flex items-center gap-3">

                    <Link
                        :href="route('pagos.index')"
                        class="text-slate-500 hover:text-slate-700"
                    >
                        &larr;
                    </Link>

                    <h1 class="text-3xl font-bold text-slate-800">
                        Registrar pago
                    </h1>

                </div>

                <p class="text-sm text-slate-500 mt-1">
                    Venta {{ venta.numero }}
                </p>

            </div>


            <!-- Resumen de la venta -->

            <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div>
                        <p class="text-sm text-slate-500">Total venta</p>
                        <p class="font-bold text-slate-800">
                            Bs. {{ formatoMoneda(venta.total) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Ya pagado</p>
                        <p class="font-bold text-emerald-600">
                            Bs. {{ formatoMoneda(totalPagado) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Saldo pendiente</p>
                        <p class="font-bold text-red-600">
                            Bs. {{ formatoMoneda(saldoRestante) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Cliente</p>
                        <p class="font-semibold text-slate-800">
                            {{ venta.clienteRelacion?.nombre ?? venta.cliente ?? 'Sin cliente' }}
                        </p>
                    </div>

                </div>

            </div>


            <!-- Formulario de pago -->

            <form
                @submit.prevent="registrar"
                class="bg-white rounded-xl border border-slate-200 p-6 space-y-6"
            >

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Fecha del pago *
                        </label>

                        <input
                            v-model="form.fecha"
                            type="date"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />

                        <p
                            v-if="form.errors.fecha"
                            class="text-sm text-red-600 mt-1"
                        >
                            {{ form.errors.fecha }}
                        </p>

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Monto a pagar (Bs.) *
                        </label>

                        <input
                            v-model.number="form.monto"
                            type="number"
                            min="0.01"
                            step="0.01"
                            :max="saldoRestante"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        />

                        <p
                            v-if="form.errors.monto"
                            class="text-sm text-red-600 mt-1"
                        >
                            {{ form.errors.monto }}
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            Saldo restante después del pago:
                            <span class="font-semibold text-slate-700">
                                Bs. {{ formatoMoneda(Math.max(0, saldoRestante - form.monto)) }}
                            </span>
                        </p>

                    </div>


                    <div>

                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Método de pago *
                        </label>

                        <select
                            v-model="form.metodo"
                            class="w-full rounded-lg border-slate-300
                                   focus:border-emerald-500
                                   focus:ring-emerald-500"
                        >
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="qr">QR</option>
                            <option value="otro">Otro</option>
                        </select>

                    </div>

                </div>


                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Observaciones
                    </label>

                    <textarea
                        v-model="form.observaciones"
                        rows="2"
                        placeholder="Nota sobre el pago..."
                        class="w-full rounded-lg border-slate-300
                               focus:border-emerald-500
                               focus:ring-emerald-500"
                    ></textarea>

                </div>


                <div class="flex justify-between items-center pt-4 border-t border-slate-200">

                    <Button
                        color="secondary"
                        type="button"
                        @click="pagarSaldoCompleto"
                    >
                        Pagar saldo completo (Bs. {{ formatoMoneda(saldoRestante) }})
                    </Button>

                    <div class="flex gap-3">

                        <Link :href="route('pagos.index')">
                            <Button color="secondary">
                                Cancelar
                            </Button>
                        </Link>

                        <Button
                            type="submit"
                            :disabled="form.processing || form.monto <= 0 || form.monto > saldoRestante"
                        >
                            {{ form.processing ? 'Registrando...' : 'Registrar pago' }}
                        </Button>

                    </div>

                </div>

            </form>

        </div>

    </AppLayout>

</template>
