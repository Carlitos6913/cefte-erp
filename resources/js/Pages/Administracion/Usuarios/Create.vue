<script setup>

import Form from './Form.vue'

defineProps({
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
})

</script>

<template>

<Form
    :roles="roles"
    :modulosDisponibles="modulosDisponibles"
    :empleados="empleados"
/>

</template>
