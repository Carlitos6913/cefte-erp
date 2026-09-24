<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'

defineProps({
    usuario: Object,
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
})

</script>

<template>

<AppLayout>

<Form
    :usuario="usuario"
    :roles="roles"
    :modulosDisponibles="modulosDisponibles"
    :empleados="empleados"
    :editing="true"
/>

</AppLayout>

</template>
