import { AUTO_DETECT_RULES } from './constants'

export function detectField(header, sampleValues = []) {
  const h = header.toLowerCase().trim()
  if (/^[éeè]valuation$/i.test(h.replace(/\s+/g, ''))) {
    const nums = sampleValues.map(v => parseFloat(String(v).replace(',', '.'))).filter(n => !isNaN(n))
    if (nums.length > 0 && nums.every(n => n >= 1 && n <= 5)) return 'rating'
    return 'text'
  }
  for (const rule of AUTO_DETECT_RULES) {
    if (rule.required && !rule.required.every(r => h.includes(r))) continue
    if (rule.patterns.some(p => h.includes(p))) return rule.field
  }
  return '_ignore'
}

/* ── Helpers CSV ─────────────────────────────────────────────────── */
export function parseCSV(text) {
  const firstNewline = text.indexOf('\n')
  const firstLine = (firstNewline >= 0 ? text.slice(0, firstNewline) : text).replace(/\r$/, '').trim()
  const sep = firstLine.includes(';') ? ';' : firstLine.includes('\t') ? '\t' : ','

  const rows = []
  let row = []
  let current = ''
  let inQuotes = false

  for (let i = 0; i < text.length; i++) {
    const c = text[i]
    if (inQuotes) {
      if (c === '"') {
        if (text[i + 1] === '"') { current += '"'; i++ }
        else inQuotes = false
      } else if (c === '\r') {
        // ignore
      } else {
        current += c
      }
    } else {
      if (c === '"') {
        inQuotes = true
      } else if (c === sep) {
        row.push(current.trim())
        current = ''
      } else if (c === '\r') {
        // ignore
      } else if (c === '\n') {
        row.push(current.trim())
        current = ''
        if (row.some(cell => cell !== '')) rows.push(row)
        row = []
      } else {
        current += c
      }
    }
  }
  if (current || row.length > 0) {
    row.push(current.trim())
    if (row.some(cell => cell !== '')) rows.push(row)
  }
  if (rows.length < 2) return null
  return { headers: rows[0], rows: rows.slice(1), sep }
}

export function mapRowToSJ(rawRow, headers, columnMap) {
  const mapped = {}
  headers.forEach((header, i) => {
    const field = columnMap[i]
    if (field && field !== '_ignore') {
      mapped[field] = (rawRow[i] ?? '').trim()
    }
  })
  if (mapped.rating) {
    const parsed = parseFloat(String(mapped.rating).replace(',', '.'))
    mapped.rating = !isNaN(parsed) ? Math.round(Math.min(5, Math.max(1, parsed))) : 0
  }
  return mapped
}
