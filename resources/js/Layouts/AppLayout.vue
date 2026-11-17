<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Sidebar from '@/Components/Layout/Sidebar.vue'
import Navbar from '@/Components/Layout/Navbar.vue'
import { useSidebar } from '@/stores/useSidebar'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})

const { mobileOpen, closeMobile } = useSidebar()
</script>

<template>
    <div class="h-screen bg-gradient-to-br from-slate-50 via-slate-50 to-emerald-50/40 flex overflow-hidden">

        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
                @click="closeMobile"
            />
        </Transition>

        <Sidebar />

        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <Navbar />

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                <div class="w-full max-w-[1600px] mx-auto">

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
                            class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 flex items-center gap-3 shadow-sm"
                        >
                            <span class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="text-sm font-medium">{{ flash.success }}</span>
                        </div>

                        <div
                            v-if="flash.error"
                            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 flex items-center gap-3 shadow-sm"
                        >
                            <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z" /></svg>
                            </span>
                            <span class="text-sm font-medium">{{ flash.error }}</span>
                        </div>

                        <slot />
                    </div>
                </Transition>

                </div>

            </main>

        </div>

    </div>
</template>
