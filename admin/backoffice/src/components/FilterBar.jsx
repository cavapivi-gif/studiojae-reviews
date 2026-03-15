import { cn } from '@/lib/utils'
import { SOURCE_LABELS, SOURCE_OPTIONS } from '../lib/constants'

const PERIODS = [
  { value: '7d',     label: '7j' },
  { value: '30d',    label: '30j' },
  { value: '90d',    label: '90j' },
  { value: '12m',    label: '12m' },
  { value: 'all',    label: 'Tout' },
  { value: 'custom', label: 'Période…' },
]

const PERIOD_LABELS = {
  '7d': '7 derniers jours',
  '30d': '30 derniers jours',
  '90d': '90 derniers jours',
  '12m': '12 derniers mois',
  'all': 'Toute la période',
  'custom': 'Période custom',
}

const GRANULARITIES = [
  { value: 'day',   label: 'J' },
  { value: 'week',  label: 'S' },
  { value: 'month', label: 'M' },
]

const selectClass = 'text-xs border rounded-md px-2 py-1.5 bg-transparent transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring'

const selectActiveClass = 'border-primary/40 text-primary bg-primary/5 font-medium'
const selectIdleClass   = 'border-input text-foreground'

const pillClass = (active) => cn(
  'px-2.5 py-1.5 text-xs font-medium rounded-md transition-colors border',
  active
    ? 'bg-primary text-primary-foreground border-primary shadow-sm'
    : 'bg-secondary text-muted-foreground border-transparent hover:bg-accent hover:text-foreground'
)

function FilterChip({ label, onRemove }) {
  return (
    <span className="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full">
      {label}
      <button
        type="button"
        onClick={onRemove}
        className="ml-0.5 text-indigo-400 hover:text-indigo-700 transition-colors leading-none"
        aria-label={`Retirer le filtre ${label}`}
      >
        ×
      </button>
    </span>
  )
}

export default function FilterBar({
  period, setPeriod,
  granularity, setGranularity,
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

  const resetAll = () => {
    handlePeriodClick('all')
    setSourceFilter('')
    setLieuFilter('')
  }

  // Determine active non-default filters for chips
  const periodActive  = period !== 'all'
  const sourceActive  = !!sourceFilter
  const lieuActive    = !!lieuFilter
  const customActive  = period === 'custom' && fromDate && toDate
  const hasAnyFilter  = periodActive || sourceActive || lieuActive

  const lieuLabel = activeLieux.find(l => l.id === lieuFilter)?.name ?? lieuFilter

  return (
    <div className="px-6 mt-6 flex flex-col gap-3">
      {/* Ligne 1 : Période + dates custom + source + lieu */}
      <div className="flex items-center gap-3 flex-wrap">

        {/* Period pills */}
        <div className="flex items-center gap-1" role="radiogroup" aria-label="Période">
          {PERIODS.map(p => (
            <button
              key={p.value}
              onClick={() => handlePeriodClick(p.value)}
              role="radio"
              aria-checked={period === p.value}
              className={pillClass(period === p.value)}
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

        {/* Separator */}
        <span className="text-border">|</span>

        {/* Source */}
        <select
          value={sourceFilter}
          onChange={e => setSourceFilter(e.target.value)}
          className={cn(selectClass, sourceActive ? selectActiveClass : selectIdleClass)}
          aria-label="Filtrer par source"
        >
          {SOURCE_OPTIONS.map(o => (
            <option key={o.value} value={o.value}>{o.label}</option>
          ))}
        </select>

        {/* Lieu */}
        {activeLieux.length > 0 && (
          <select
            value={lieuFilter}
            onChange={e => setLieuFilter(e.target.value)}
            className={cn(selectClass, lieuActive ? selectActiveClass : selectIdleClass)}
            aria-label="Filtrer par lieu"
          >
            <option value="">Tous les lieux</option>
            {activeLieux.map(l => (
              <option key={l.id} value={l.id}>{l.name}</option>
            ))}
          </select>
        )}
      </div>

      {/* Ligne 2 : Granularité */}
      <div className="flex items-center gap-2">
        <span className="text-[11px] text-muted-foreground uppercase tracking-wider">Granularité :</span>
        <div className="flex items-center gap-1" role="radiogroup" aria-label="Granularité">
          {GRANULARITIES.map(g => (
            <button
              key={g.value}
              onClick={() => setGranularity?.(granularity === g.value ? '' : g.value)}
              role="radio"
              aria-checked={granularity === g.value}
              title={g.value === 'day' ? 'Par jour' : g.value === 'week' ? 'Par semaine' : 'Par mois'}
              className={pillClass(granularity === g.value)}
            >
              {g.label}
            </button>
          ))}
        </div>
      </div>

      {/* Ligne 3 : Chips filtres actifs */}
      {hasAnyFilter && (
        <div className="flex items-center gap-2 flex-wrap pt-0.5">
          <span className="text-[11px] text-muted-foreground">Filtres actifs :</span>

          {periodActive && !customActive && (
            <FilterChip
              label={PERIOD_LABELS[period] ?? period}
              onRemove={() => handlePeriodClick('all')}
            />
          )}
          {customActive && (
            <FilterChip
              label={`${fromDate} → ${toDate}`}
              onRemove={() => handlePeriodClick('all')}
            />
          )}
          {sourceActive && (
            <FilterChip
              label={SOURCE_LABELS[sourceFilter] ?? sourceFilter}
              onRemove={() => setSourceFilter('')}
            />
          )}
          {lieuActive && (
            <FilterChip
              label={lieuLabel}
              onRemove={() => setLieuFilter('')}
            />
          )}

          <button
            type="button"
            onClick={resetAll}
            className="text-[11px] text-muted-foreground hover:text-foreground underline ml-1 transition-colors"
          >
            Réinitialiser tout
          </button>
        </div>
      )}
    </div>
  )
}
