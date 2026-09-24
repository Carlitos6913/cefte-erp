import { ref, watch } from 'vue'

const collapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true')

watch(collapsed, (value) => {
    localStorage.setItem('sidebar-collapsed', String(value))
})

export function useSidebar() {
    const toggle = () => {
        collapsed.value = !collapsed.value
    }

    return { collapsed, toggle }
}