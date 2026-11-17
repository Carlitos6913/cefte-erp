<script setup>
defineProps({
    headers: Array
})
</script>

<template>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full table-responsive text-sm">

            <thead class="bg-slate-50 border-b border-slate-200">

                <tr>

                    <th
                        v-for="header in headers"
                        :key="header"
                        scope="col"
                        class="text-left px-5 py-3.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap"
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

</div>

</template>
