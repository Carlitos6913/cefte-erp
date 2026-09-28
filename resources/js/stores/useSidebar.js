import { ref, watch } from 'vue'

const collapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true')

watch(collapsed, (value) => {
    localStorage.setItem('sidebar-collapsed', String(value))
})

const mobileOpen = ref(false)

export function useSidebar() {
    const toggle = () => {
        collapsed.value = !collapsed.value
    }

    const toggleMobile = () => {
        mobileOpen.value = !mobileOpen.value
    }

    const closeMobile = () => {
        mobileOpen.value = false
    }

    return { collapsed, mobileOpen, toggle, toggleMobile, closeMobile }
}