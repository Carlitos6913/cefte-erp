<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Sidebar from '@/Components/Layout/Sidebar.vue'
import Navbar from '@/Components/Layout/Navbar.vue'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
</script>

<template>
    <div class="h-screen bg-slate-50 flex overflow-hidden">

        <Sidebar />

        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <Navbar />

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div>
                        <div
                            v-if="flash.success"
                            class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center gap-3"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ flash.success }}
                        </div>

                        <div
                            v-if="flash.error"
                            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center gap-3"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            {{ flash.error }}
                        </div>

                        <slot />
                    </div>
                </Transition>

            </main>

        </div>

    </div>
</template>
