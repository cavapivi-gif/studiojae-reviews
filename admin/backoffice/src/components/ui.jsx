// Design system SJ Reviews — noir/blanc, cohérent avec BlackTenders

export function Btn({ children, variant = 'primary', size = 'md', loading, disabled, className = '', ...props }) {
  const base = 'inline-flex items-center gap-1.5 transition-colors disabled:opacity-40 disabled:cursor-not-allowed'
  const sizes = { sm: 'px-3 py-1.5 text-xs', md: 'px-4 py-2 text-sm', lg: 'px-5 py-2.5 text-sm' }
  const variants = {
    primary:   'bg-black text-white hover:bg-gray-800',
    secondary: 'bg-white text-black border border-gray-200 hover:border-black',
    ghost:     'bg-transparent text-gray-600 hover:text-black hover:bg-gray-50',
    danger:    'bg-white text-red-600 border border-red-200 hover:bg-red-50',
  }
  return (
    <button
      disabled={disabled || loading}
      className={`${base} ${sizes[size]} ${variants[variant]} ${className}`}
      {...props}
    >
      {loading && <Spinner />}
      {children}
    </button>
  )
}

export function Input({ label, error, className = '', ...props }) {
  const field = (
    <>
      <input
        className={`w-full border ${error ? 'border-red-300' : 'border-gray-200'} px-3 py-2 text-sm outline-none focus:border-black transition-colors ${className}`}
        {...props}
      />
      {error && <span className="text-xs text-red-500">{error}</span>}
    </>
  )
  if (!label) return <div className="flex flex-col gap-1">{field}</div>
  return (
    <label className="flex flex-col gap-1">
      <span className="text-xs text-gray-500">{label}</span>
      {field}
    </label>
  )
}

export function Textarea({ label, error, className = '', ...props }) {
  const field = (
    <textarea
      className={`w-full border ${error ? 'border-red-300' : 'border-gray-200'} px-3 py-2 text-sm outline-none focus:border-black transition-colors resize-y min-h-[80px] ${className}`}
      {...props}
    />
  )
  if (!label) return field
  return (
    <label className="flex flex-col gap-1">
      <span className="text-xs text-gray-500">{label}</span>
      {field}
      {error && <span className="text-xs text-red-500">{error}</span>}
    </label>
  )
}

export function Select({ label, children, className = '', ...props }) {
  const field = (
    <select
      className={`w-full border border-gray-200 px-3 py-2 text-sm outline-none focus:border-black transition-colors bg-white ${className}`}
      {...props}
    >
      {children}
    </select>
  )
  if (!label) return field
  return (
    <label className="flex flex-col gap-1">
      <span className="text-xs text-gray-500">{label}</span>
      {field}
    </label>
  )
}

export function Toggle({ label, checked, onChange }) {
  return (
    <label className="flex items-center gap-3 cursor-pointer select-none">
      <div
        onClick={() => onChange(!checked)}
        className={`relative w-9 h-5 transition-colors ${checked ? 'bg-black' : 'bg-gray-200'}`}
        role="switch"
        aria-checked={checked}
        tabIndex={0}
        onKeyDown={e => e.key === ' ' && onChange(!checked)}
      >
        <span
          className={`absolute top-0.5 left-0.5 w-4 h-4 bg-white transition-transform ${checked ? 'translate-x-4' : ''}`}
        />
      </div>
      {label && <span className="text-sm text-gray-700">{label}</span>}
    </label>
  )
}

export function Stars({ rating, max = 5, size = 14 }) {
  return (
    <span className="inline-flex gap-0.5" aria-label={`${rating}/${max}`}>
      {Array.from({ length: max }, (_, i) => (
        <svg key={i} width={size} height={size} viewBox="0 0 24 24" aria-hidden="true">
          <path
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            fill={i < rating ? '#f5a623' : 'none'}
            stroke={i < rating ? '#f5a623' : '#d1d5db'}
            strokeWidth="1.5"
          />
        </svg>
      ))}
    </span>
  )
}

export function Badge({ children, variant = 'default' }) {
  const variants = {
    default:   'bg-gray-100 text-gray-600',
    ok:        'bg-black text-white',
    certified: 'bg-black text-white',
    warn:      'bg-gray-100 text-gray-500',
    google:    'bg-[#4285F4] text-white',
    direct:    'bg-gray-800 text-white',
  }
  return (
    <span className={`inline-block px-2 py-0.5 text-xs ${variants[variant] ?? variants.default}`}>
      {children}
    </span>
  )
}

export function Card({ children, className = '' }) {
  return (
    <div className={`border border-gray-200 bg-white ${className}`}>
      {children}
    </div>
  )
}

export function PageHeader({ title, actions }) {
  return (
    <div className="flex items-center justify-between px-8 py-6 border-b border-gray-200">
      <h1 className="text-base tracking-wide uppercase text-gray-800">{title}</h1>
      {actions && <div className="flex items-center gap-2">{actions}</div>}
    </div>
  )
}

export function Table({ columns, data, loading, empty = 'Aucune donnée.' }) {
  if (loading) return <div className="px-8 py-12 text-center text-sm text-gray-400">Chargement…</div>
  if (!data?.length) return <div className="px-8 py-12 text-center text-sm text-gray-400">{empty}</div>

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-gray-200">
            {columns.map(col => (
              <th key={col.key} className="px-4 py-3 text-left text-xs text-gray-400 uppercase tracking-widest font-normal">
                {col.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.map((row, i) => (
            <tr key={row.id ?? i} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
              {columns.map(col => (
                <td key={col.key} className="px-4 py-3 text-gray-700">
                  {col.render ? col.render(row) : row[col.key] ?? '—'}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

export function Spinner({ size = 14 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="animate-spin">
      <circle cx="12" cy="12" r="10" strokeOpacity=".25" />
      <path d="M12 2a10 10 0 0 1 10 10" />
    </svg>
  )
}

export function Notice({ type = 'info', children }) {
  const colors = {
    info:    'border-gray-200 bg-gray-50 text-gray-600',
    success: 'border-gray-800 bg-black text-white',
    error:   'border-red-200 bg-red-50 text-red-700',
    warn:    'border-yellow-200 bg-yellow-50 text-yellow-800',
  }
  return (
    <div className={`border px-4 py-3 text-sm ${colors[type]}`}>
      {children}
    </div>
  )
}

export function StatCard({ label, value, sub, accent = false }) {
  return (
    <div className={`border border-gray-200 px-6 py-5 ${accent ? 'bg-black text-white' : ''}`}>
      <div className={`text-xs uppercase tracking-widest mb-2 ${accent ? 'text-gray-400' : 'text-gray-400'}`}>{label}</div>
      <div className="text-3xl">{value ?? '—'}</div>
      {sub && <div className={`text-xs mt-1 ${accent ? 'text-gray-400' : 'text-gray-400'}`}>{sub}</div>}
    </div>
  )
}

export function Pagination({ page, total, perPage, onChange }) {
  const totalPages = Math.ceil(total / perPage)
  if (totalPages <= 1) return null
  return (
    <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 text-xs text-gray-500">
      <span>{total} avis</span>
      <div className="flex items-center gap-1">
        <Btn size="sm" variant="ghost" disabled={page <= 1} onClick={() => onChange(page - 1)}>Précédent</Btn>
        <span className="px-3">{page} / {totalPages}</span>
        <Btn size="sm" variant="ghost" disabled={page >= totalPages} onClick={() => onChange(page + 1)}>Suivant</Btn>
      </div>
    </div>
  )
}

export function StarPicker({ value, onChange }) {
  return (
    <div className="flex gap-1">
      {[1, 2, 3, 4, 5].map(n => (
        <button
          key={n}
          type="button"
          onClick={() => onChange(n)}
          className="p-0.5 transition-transform hover:scale-110"
          aria-label={`${n} étoile${n > 1 ? 's' : ''}`}
        >
          <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
              fill={n <= value ? '#f5a623' : 'none'}
              stroke={n <= value ? '#f5a623' : '#d1d5db'}
              strokeWidth="1.5"
            />
          </svg>
        </button>
      ))}
    </div>
  )
}

export function RatingBar({ value, max, count }) {
  const pct = max > 0 ? Math.round((count / max) * 100) : 0
  return (
    <div className="flex items-center gap-2 text-xs">
      <span className="w-2 text-gray-500">{value}</span>
      <div className="flex-1 h-1 bg-gray-100">
        <div className="h-full bg-black transition-all" style={{ width: `${pct}%` }} />
      </div>
      <span className="w-6 text-right text-gray-400">{count}</span>
    </div>
  )
}
