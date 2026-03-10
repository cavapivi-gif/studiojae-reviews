import { cn } from '@/lib/utils'
import { SOURCE_OPTIONS } from '../lib/constants'

const PERIODS = [
  { value: '7d', label: '7 jours' },
  { value: '30d', label: '30 jours' },
  { value: '90d', label: '90 jours' },
  { value: '12m', label: '12 mois' },
  { value: 'all', label: 'Tout' },
  { value: 'custom', label: 'Période' },
]

const selectClass = 'text-xs border border-input rounded-md px-2 py-1.5 bg-transparent text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring'

export default function FilterBar({
  period, setPeriod,
  sourceFilter, setSourceFilter,
  lieuFilter, setLieuFilter,
  lieux = [],
  fromDate = '', setFromDate,
  toDate = '', setToDate,
}) {
  const activeLieux = lieux.filter(l => l.active)

  const handlePeriodClick = (value) => {
    setPeriod(value)
    if (value !== 'custom') {
      setFromDate?.('')
      setToDate?.('')
    }
  }

  return (
    <div className="px-6 mt-6 flex items-center gap-4 flex-wrap">
      {/* Period pills */}
      <div className="flex items-center gap-1" role="radiogroup" aria-label="Période">
        {PERIODS.map(p => (
          <button
            key={p.value}
            onClick={() => handlePeriodClick(p.value)}
            role="radio"
            aria-checked={period === p.value}
            aria-pressed={period === p.value}
            className={cn(
              'px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
              period === p.value
                ? 'bg-primary text-primary-foreground'
                : 'bg-secondary text-muted-foreground hover:bg-accent hover:text-foreground'
            )}
          >
            {p.label}
          </button>
        ))}
      </div>

      {/* Custom date range */}
      {period === 'custom' && (
        <div className="flex items-center gap-2">
          <input
            type="date"
            value={fromDate}
            onChange={e => setFromDate?.(e.target.value)}
            className={selectClass}
            aria-label="Date de début"
          />
          <span className="text-xs text-muted-foreground">→</span>
          <input
            type="date"
            value={toDate}
            onChange={e => setToDate?.(e.target.value)}
            className={selectClass}
            aria-label="Date de fin"
          />
        </div>
      )}

      {/* Source dropdown */}
      <select
        value={sourceFilter}
        onChange={e => setSourceFilter(e.target.value)}
        className={selectClass}
        aria-label="Filtrer par source"
      >
        {SOURCE_OPTIONS.map(o => (
          <option key={o.value} value={o.value}>{o.label}</option>
        ))}
      </select>

      {/* Lieu dropdown */}
      {activeLieux.length > 0 && (
        <select
          value={lieuFilter}
          onChange={e => setLieuFilter(e.target.value)}
          className={selectClass}
          aria-label="Filtrer par lieu"
        >
          <option value="">Tous les lieux</option>
          {activeLieux.map(l => (
            <option key={l.id} value={l.id}>{l.name}</option>
          ))}
        </select>
      )}
    </div>
  )
}
