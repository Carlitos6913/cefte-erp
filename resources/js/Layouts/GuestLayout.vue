<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-950 flex items-center justify-center px-4 py-10">

        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950"></div>

        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-32 w-[28rem] h-[28rem] rounded-full bg-emerald-600/10 blur-3xl"></div>

        <div class="relative z-10 w-full max-w-md">

            <div class="text-center mb-8">
                <Link href="/" class="inline-block">
                    <div class="text-4xl font-black tracking-tight text-white">
                        CEFTE
                    </div>
                </Link>
                <p class="mt-3 text-xs text-slate-400">
                    Sistema de gestión de producción y ventas
                </p>
            </div>

            <div class="bg-white rounded-3xl border border-white/10 shadow-2xl shadow-black/40 px-6 py-8 sm:px-8">
                <slot />
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                © {{ new Date().getFullYear() }} CEFTE
            </p>

        </div>

    </div>
</template>
