<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'

defineProps({
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
})

</script>

<template>

<AppLayout>

<Form
    :roles="roles"
    :modulosDisponibles="modulosDisponibles"
    :empleados="empleados"
/>

</AppLayout>

</template>
