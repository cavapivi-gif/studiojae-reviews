// Design system SJ Reviews — uses shadcn CSS variables for consistency

import { cn } from '@/lib/utils'
import { Progress } from '@/components/ui/progress'

export function Btn({ children, variant = 'primary', size = 'md', loading, disabled, className = '', ...props }) {
  const base = 'inline-flex items-center justify-center gap-1.5 font-medium transition-colors disabled:opacity-50 disabled:pointer-events-none rounded-md'
  const sizes = { sm: 'px-3 py-1.5 text-xs h-8', md: 'px-4 py-2 text-sm h-9', lg: 'px-5 py-2.5 text-sm h-10' }
  const variants = {
    primary:   'bg-primary text-primary-foreground hover:bg-primary/90',
    secondary: 'bg-secondary text-secondary-foreground border border-border hover:bg-accent',
    ghost:     'text-muted-foreground hover:text-foreground hover:bg-accent',
    danger:    'bg-secondary text-destructive border border-destructive/20 hover:bg-destructive/10',
  }
  return (
    <button
      disabled={disabled || loading}
      className={cn(base, sizes[size], variants[variant], className)}
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
        className={cn(
          'flex h-9 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-sm transition-colors',
          'file:border-0 file:bg-transparent file:text-sm file:font-medium',
          'placeholder:text-muted-foreground',
          'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
          'disabled:cursor-not-allowed disabled:opacity-50',
          error ? 'border-destructive' : 'border-input',
          className
        )}
        {...props}
      />
      {error && <span className="text-xs text-destructive">{error}</span>}
    </>
  )
  if (!label) return <div className="flex flex-col gap-1.5">{field}</div>
  return (
    <label className="flex flex-col gap-1.5">
      <span className="text-sm font-medium">{label}</span>
      {field}
    </label>
  )
}

export function Textarea({ label, error, className = '', ...props }) {
  const field = (
    <textarea
      className={cn(
        'flex min-h-[80px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-sm transition-colors',
        'placeholder:text-muted-foreground',
        'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
        'disabled:cursor-not-allowed disabled:opacity-50 resize-y',
        error ? 'border-destructive' : 'border-input',
        className
      )}
      {...props}
    />
  )
  const errorEl = error && <span className="text-xs text-destructive">{error}</span>
  if (!label) return <div className="flex flex-col gap-1.5">{field}{errorEl}</div>
  return (
    <label className="flex flex-col gap-1.5">
      <span className="text-sm font-medium">{label}</span>
      {field}
      {errorEl}
    </label>
  )
}

export function Select({ label, children, className = '', ...props }) {
  const field = (
    <select
      className={cn(
        'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors',
        'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
        className
      )}
      {...props}
    >
      {children}
    </select>
  )
  if (!label) return field
  return (
    <label className="flex flex-col gap-1.5">
      <span className="text-sm font-medium">{label}</span>
      {field}
    </label>
  )
}

export function Toggle({ label, checked, onChange }) {
  return (
    <label className="flex items-center gap-3 cursor-pointer select-none">
      <button
        type="button"
        role="switch"
        aria-checked={checked}
        onClick={() => onChange(!checked)}
        className={cn(
          'relative inline-flex h-6 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200',
          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
          checked ? 'bg-emerald-500' : 'bg-gray-300'
        )}
      >
        <span
          className={cn(
            'pointer-events-none block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition-transform duration-200',
            checked ? 'translate-x-4' : 'translate-x-0'
          )}
        />
      </button>
      {label && <span className="text-sm">{label}</span>}
    </label>
  )
}

export function Stars({ rating, max = 5, size = 14 }) {
  const r = typeof rating === 'number' ? rating : parseFloat(rating) || 0
  return (
    <span className="inline-flex gap-0.5" aria-label={`${r}/${max}`}>
      {Array.from({ length: max }, (_, i) => {
        const fill = Math.min(1, Math.max(0, r - i))
        const isFull = fill >= 0.75
        const isHalf = fill >= 0.25 && fill < 0.75
        const gradId = `sj-star-g-${i}-${Math.round(r * 10)}`
        return (
          <svg key={i} width={size} height={size} viewBox="0 0 24 24" aria-hidden="true">
            {isHalf && (
              <defs>
                <linearGradient id={gradId}>
                  <stop offset="50%" stopColor="#f5a623" />
                  <stop offset="50%" stopColor="#d1d5db" />
                </linearGradient>
              </defs>
            )}
            <path
              d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
              fill={isFull ? '#f5a623' : isHalf ? `url(#${gradId})` : 'none'}
              stroke={isFull || isHalf ? '#f5a623' : '#d1d5db'}
              strokeWidth="1.5"
            />
          </svg>
        )
      })}
    </span>
  )
}

export function Badge({ children, variant = 'default' }) {
  const variants = {
    default:     'bg-secondary text-secondary-foreground',
    ok:          'bg-emerald-50 text-emerald-700 border border-emerald-200',
    certified:   'bg-emerald-50 text-emerald-700 border border-emerald-200',
    warn:        'bg-amber-50 text-amber-700 border border-amber-200',
    google:      'bg-[#4285F4]/10 text-[#4285F4] border border-[#4285F4]/20',
    tripadvisor: 'bg-[#00AF87]/10 text-[#00AF87] border border-[#00AF87]/20',
    facebook:    'bg-[#1877F2]/10 text-[#1877F2] border border-[#1877F2]/20',
    trustpilot:  'bg-[#00B67A]/10 text-[#00B67A] border border-[#00B67A]/20',
    regiondo:    'bg-[#e85c2c]/10 text-[#e85c2c] border border-[#e85c2c]/20',
    direct:      'bg-primary/10 text-primary border border-primary/20',
    autre:       'bg-secondary text-muted-foreground border border-border',
  }
  return (
    <span className={cn('inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-md', variants[variant] ?? variants.default)}>
      {children}
    </span>
  )
}

export function Card({ children, className = '' }) {
  return (
    <div className={cn('rounded-lg border bg-card text-card-foreground shadow-sm', className)}>
      {children}
    </div>
  )
}

export function PageHeader({ title, subtitle, actions }) {
  return (
    <div className="flex items-center justify-between px-6 py-5 border-b">
      <div>
        <h1 className="text-base font-medium tracking-tight">{title}</h1>
        {subtitle && <p className="text-sm text-muted-foreground mt-0.5">{subtitle}</p>}
      </div>
      {actions && <div className="flex items-center gap-2">{actions}</div>}
    </div>
  )
}

export function Table({ columns, data, loading, empty = 'Aucune donnée.' }) {
  if (loading) return <div className="px-6 py-12 text-center text-sm text-muted-foreground">Chargement…</div>
  if (!data?.length) return <div className="px-6 py-12 text-center text-sm text-muted-foreground">{empty}</div>

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b">
            {columns.map(col => (
              <th key={col.key} className="px-4 py-3 text-left text-xs text-muted-foreground uppercase tracking-wider font-normal">
                {col.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.map((row, i) => (
            <tr key={row.id ?? i} className="border-b transition-colors hover:bg-muted/50">
              {columns.map(col => (
                <td key={col.key} className="px-4 py-3">
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
    info:    'border bg-muted text-muted-foreground',
    success: 'border-primary bg-primary text-primary-foreground',
    error:   'border-destructive/50 bg-destructive/10 text-destructive',
    warn:    'border-amber-200 bg-amber-50 text-amber-700',
  }
  return (
    <div className={cn('rounded-md px-4 py-3 text-sm', colors[type])}>
      {children}
    </div>
  )
}

export function StatCard({ label, value, sub, accent = false }) {
  return (
    <div className={cn(
      'px-6 py-5',
      accent ? 'bg-primary text-primary-foreground' : 'bg-card'
    )}>
      <div className={cn('text-[11px] uppercase tracking-wider mb-2', accent ? 'text-primary-foreground/60' : 'text-muted-foreground')}>{label}</div>
      <div className="text-2xl">{value ?? '—'}</div>
      {sub && <div className={cn('text-xs mt-1.5', accent ? 'text-primary-foreground/60' : 'text-muted-foreground')}>{sub}</div>}
    </div>
  )
}

export function Pagination({ page, total, perPage, onChange }) {
  const totalPages = Math.ceil(total / perPage)
  if (totalPages <= 1) return null
  return (
    <div className="flex items-center justify-between px-4 py-3 border-t text-sm text-muted-foreground">
      <span>{total} avis</span>
      <div className="flex items-center gap-1">
        <Btn size="sm" variant="ghost" disabled={page <= 1} onClick={() => onChange(page - 1)}>Précédent</Btn>
        <span className="px-3 tabular-nums">{page} / {totalPages}</span>
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
      <span className="w-2 text-muted-foreground">{value}</span>
      <Progress value={pct} className="h-2 flex-1" />
      <span className="w-6 text-right text-muted-foreground tabular-nums">{count}</span>
    </div>
  )
}
