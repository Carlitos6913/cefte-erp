export function formatearMoneda(valor) {
    return `Bs. ${new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(valor ?? 0))}`
}

export function formatearNumero(valor) {
    return new Intl.NumberFormat('es-BO', {
        maximumFractionDigits: 2,
    }).format(Number(valor ?? 0))
}

export function fechaLocal(fecha) {
    if (!fecha) return null

    const [year, month, day] = String(fecha).slice(0, 10).split('-').map(Number)

    if (!year || !month || !day) return null

    return new Date(year, month - 1, day)
}

export function formatearFecha(fecha) {
    const valor = fechaLocal(fecha)

    return valor ? valor.toLocaleDateString('es-BO') : '-'
}

export function formatearFechaCorta(fecha) {
    const valor = fechaLocal(fecha)

    if (!valor) return '-'

    return valor.toLocaleDateString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).replace('.', '')
}
