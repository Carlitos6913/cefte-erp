<script setup>

defineProps({

    color:{
        type:String,
        default:'primary'
    }

})

</script>

<template>

<button
class="px-4 py-2 rounded-lg font-medium transition"

:class="{

'bg-emerald-600 text-white hover:bg-emerald-700':color==='primary',

'bg-blue-600 text-white hover:bg-blue-700':color==='info',

'bg-red-600 text-white hover:bg-red-700':color==='danger',

'bg-gray-200 hover:bg-gray-300':color==='secondary',

'bg-green-600 text-white hover:bg-green-700':color==='success'

}"

>

<slot/>

</button>

</template>
