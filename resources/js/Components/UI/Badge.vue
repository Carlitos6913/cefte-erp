<script setup>

defineProps({

color:String

})

</script>

<template>

<span
class="px-3 py-1 rounded-full text-xs font-semibold"

:class="{

'bg-emerald-100 text-emerald-700':color=='green',

'bg-red-100 text-red-700':color=='red',

'bg-blue-100 text-blue-700':color=='blue',

'bg-yellow-100 text-yellow-700':color=='yellow',

'bg-gray-100 text-gray-700':color=='gray',

'bg-indigo-100 text-indigo-700':color=='indigo',

'bg-orange-100 text-orange-700':color=='orange',

'bg-purple-100 text-purple-700':color=='purple'

}"

>

<slot/>

</span>

</template>
