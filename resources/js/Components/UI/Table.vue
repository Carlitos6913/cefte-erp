<script setup>
defineProps({
    headers: Array
})
</script>

<template>

<div class="bg-white rounded-xl shadow overflow-x-auto">

    <table class="w-full">

        <thead class="bg-gray-50 border-b">

            <tr>

                <th
                    v-for="header in headers"
                    :key="header"
                    class="text-left p-4 font-semibold text-gray-700"
                >
                    {{ header }}
                </th>

            </tr>

        </thead>

        <tbody>

            <slot/>

        </tbody>

    </table>

</div>

</template>
