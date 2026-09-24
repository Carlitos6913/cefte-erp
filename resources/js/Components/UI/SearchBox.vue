<script setup>

defineProps({
    modelValue: String
})

defineEmits(['update:modelValue'])

</script>

<template>

<input
    :value="modelValue"
    @input="$emit('update:modelValue',$event.target.value)"
    class="border rounded-lg px-4 py-2 w-72"
    placeholder="Buscar..."
>

</template>
