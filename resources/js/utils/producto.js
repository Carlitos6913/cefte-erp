export function normalizarTexto(valor) {
    return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

export function capitalizar(valor) {
    const texto = String(valor ?? '').trim()

    if (!texto) return ''

    return texto.charAt(0).toUpperCase() + texto.slice(1)
}

export function formatearCantidad(valor) {
    const numero = Number(valor)

    if (!Number.isFinite(numero)) return ''

    return numero.toFixed(2).replace(/\.?0+$/, '')
}

export function simboloUnidad(variante) {
    return (
        variante?.unidad_base?.symbol
        ?? variante?.unidadBase?.symbol
        ?? variante?.unit_measure?.symbol
        ?? variante?.unitMeasure?.symbol
        ?? 'und'
    )
}

export function simboloContenido(variante) {
    return (
        variante?.unit_measure?.symbol
        ?? variante?.unitMeasure?.symbol
        ?? variante?.unidad_base?.symbol
        ?? variante?.unidadBase?.symbol
        ?? ''
    )
}

export function presentacionVariante(variante) {
    if (!variante) return ''

    const partes = []

    const sabor = capitalizar(variante.flavor)

    if (sabor) partes.push(sabor)

    const empaque = capitalizar(variante.packaging_type)
    const contenido = Number(variante.net_content) > 0
        ? `${formatearCantidad(variante.net_content)} ${simboloContenido(variante)}`.trim()
        : ''

    const envaseContenido = [empaque, contenido].filter(Boolean).join(' ')

    if (envaseContenido) partes.push(envaseContenido)

    return partes.join(' · ')
}

export function disponibleVariante(variante, modo = 'salida') {
    const valor = modo === 'venta'
        ? variante?.saldo_autorizado
        : variante?.stock

    return Number(valor ?? 0)
}

export function formatearFechaCorta(valor) {
    if (!valor) return '-'

    return new Date(valor).toLocaleDateString('es-BO')
}

export function lotesFefo(variante) {
    return [...(variante?.lotes ?? [])]
        .filter((lote) => Number(lote.stock) > 0)
        .sort((a, b) => {
            const caducidadA = new Date(a.fecha_caducidad).getTime()
            const caducidadB = new Date(b.fecha_caducidad).getTime()

            if (caducidadA !== caducidadB) return caducidadA - caducidadB

            return Number(a.id) - Number(b.id)
        })
}

export function repartirFefo(variante, cantidad) {
    let pendiente = Number(cantidad ?? 0)
    const asignaciones = []

    if (!Number.isFinite(pendiente) || pendiente <= 0) {
        return { asignaciones, pendiente: 0 }
    }

    for (const lote of lotesFefo(variante)) {
        if (pendiente <= 0) break

        const disponible = Number(lote.stock ?? 0)

        if (disponible <= 0) continue

        const tomar = Math.min(disponible, pendiente)

        asignaciones.push({ ...lote, cantidad_asignada: tomar })
        pendiente -= tomar
    }

    return { asignaciones, pendiente }
}
