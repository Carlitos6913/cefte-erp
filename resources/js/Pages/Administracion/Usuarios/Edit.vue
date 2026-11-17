<script setup>

import Form from './Form.vue'

defineProps({
    usuario: Object,
    roles: Array,
    modulosDisponibles: Object,
    empleados: Array,
})

</script>

<template>

<Form
    :usuario="usuario"
    :roles="roles"
    :modulosDisponibles="modulosDisponibles"
    :empleados="empleados"
    :editing="true"
/>

</template>
