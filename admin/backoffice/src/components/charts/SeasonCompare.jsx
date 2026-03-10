import { useState, useMemo } from 'react'
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid, Legend } from 'recharts'
import { SEASONS, SOURCE_LABELS, SOURCE_HEX } from '../../lib/constants'
import { Spinner } from '../ui'

const currentYear = new Date().getFullYear()
const YEARS = Array.from({ length: 15 }, (_, i) => currentYear - i)

const SEASON_OPTIONS = Object.entries(SEASONS).map(([k, v]) => ({ value: k, label: v.label }))

function SeasonSelector({ label, season, year, onSeasonChange, onYearChange }) {
  return (
    <div className="flex items-center gap-2">
      <span className="text-xs text-gray-400">{label}</span>
      <select
        value={season}
        onChange={e => onSeasonChange(e.target.value)}
        className="text-xs border border-gray-200 pl-2 pr-6 py-1 bg-white text-gray-600 appearance-auto"
      >
        {SEASON_OPTIONS.map(o => (
          <option key={o.value} value={o.value}>{o.label}</option>
        ))}
      </select>
      <select
        value={year}
        onChange={e => onYearChange(parseInt(e.target.value))}
        className="text-xs border border-gray-200 pl-2 pr-6 py-1 bg-white text-gray-600 appearance-auto"
      >
        {YEARS.map(y => (
          <option key={y} value={y}>{y}</option>
        ))}
      </select>
    </div>
  )
}

function DateRangeSelector({ label, from, to, onFromChange, onToChange }) {
  return (
    <div className="flex items-center gap-2">
      <span className="text-xs text-gray-400">{label}</span>
      <input
        type="date"
        value={from}
        onChange={e => onFromChange(e.target.value)}
        className="text-xs border border-gray-200 px-2 py-1 bg-white text-gray-600"
      />
      <span className="text-xs text-gray-300">→</span>
      <input
        type="date"
        value={to}
        onChange={e => onToChange(e.target.value)}
        className="text-xs border border-gray-200 px-2 py-1 bg-white text-gray-600"
      />
    </div>
  )
}

function DeltaBadge({ current, previous }) {
  if (!previous) return null
  const delta = ((current - previous) / previous) * 100
  const sign = delta > 0 ? '+' : ''
  const color = delta > 0 ? 'text-green-600' : delta < 0 ? 'text-red-600' : 'text-gray-400'
  return (
    <span className={`text-xs font-medium ${color}`}>
      {sign}{delta.toFixed(0)}%
    </span>
  )
}

function periodLabel(p) {
  // For season-based comparison
  if (p.season) return `${SEASONS[p.season]?.label ?? p.season} ${p.year}`
  // For date range comparison
  if (p.label) return p.label
  return ''
}

export default function SeasonCompare({ comparison, loading, onCompare, onCompareRange }) {
  const [mode, setMode] = useState('season') // 'season' | 'range'
  const [season1, setSeason1] = useState('summer')
  const [year1, setYear1] = useState(currentYear)
  const [season2, setSeason2] = useState('summer')
  const [year2, setYear2] = useState(currentYear - 1)

  // Custom range state
  const [from1, setFrom1] = useState('')
  const [to1, setTo1] = useState('')
  const [from2, setFrom2] = useState('')
  const [to2, setTo2] = useState('')

  const handleCompare = () => {
    if (mode === 'season') {
      onCompare(season1, year1, season2, year2)
    } else if (onCompareRange && from1 && to1 && from2 && to2) {
      onCompareRange(from1, to1, from2, to2)
    }
  }

  const periods = comparison?.periods
  const p1 = periods?.[0]
  const p2 = periods?.[1]

  const label1 = p1 ? periodLabel(p1) : ''
  const label2 = p2 ? periodLabel(p2) : ''

  // Distribution comparison chart data
  const distData = useMemo(() => {
    if (!p1 || !p2) return []
    return [5, 4, 3, 2, 1].map(n => ({
      rating: `${n}★`,
      [label1]: p1.distribution?.[n] ?? 0,
      [label2]: p2.distribution?.[n] ?? 0,
    }))
  }, [p1, p2, label1, label2])

  return (
    <div>
      {/* Mode tabs */}
      <div className="flex items-center gap-1 mb-4">
        <button
          onClick={() => setMode('season')}
          className={`px-3 py-1.5 text-xs font-medium transition-colors ${mode === 'season' ? 'bg-black text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`}
        >
          Saisons
        </button>
        <button
          onClick={() => setMode('range')}
          className={`px-3 py-1.5 text-xs font-medium transition-colors ${mode === 'range' ? 'bg-black text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`}
        >
          Dates personnalisées
        </button>
      </div>

      {/* Selectors */}
      <div className="flex items-center gap-6 flex-wrap">
        {mode === 'season' ? (
          <>
            <SeasonSelector label="Période A" season={season1} year={year1} onSeasonChange={setSeason1} onYearChange={setYear1} />
            <span className="text-gray-300 text-xs">vs</span>
            <SeasonSelector label="Période B" season={season2} year={year2} onSeasonChange={setSeason2} onYearChange={setYear2} />
          </>
        ) : (
          <>
            <DateRangeSelector label="Période A" from={from1} to={to1} onFromChange={setFrom1} onToChange={setTo1} />
            <span className="text-gray-300 text-xs">vs</span>
            <DateRangeSelector label="Période B" from={from2} to={to2} onFromChange={setFrom2} onToChange={setTo2} />
          </>
        )}
        <button
          onClick={handleCompare}
          disabled={mode === 'range' && (!from1 || !to1 || !from2 || !to2)}
          className="px-3 py-1.5 text-xs font-medium bg-black text-white hover:bg-gray-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Comparer
        </button>
      </div>

      {loading && (
        <div className="flex justify-center py-8"><Spinner size={16} /></div>
      )}

      {p1 && p2 && !loading && (
        <div className="mt-5 space-y-5">
          {/* Summary cards */}
          <div className="grid grid-cols-2 gap-4">
            {[p1, p2].map((p, i) => (
              <div key={i} className="border border-gray-200 p-4">
                <div className="text-xs text-gray-400 uppercase tracking-wider mb-2">
                  {i === 0 ? label1 : label2}
                </div>
                <div className="flex items-baseline gap-3">
                  <span className="text-2xl font-semibold">{p.total}</span>
                  <span className="text-xs text-gray-400">avis</span>
                  {i === 0 && <DeltaBadge current={p1.total} previous={p2.total} />}
                </div>
                <div className="flex items-center gap-2 mt-1">
                  <span className="text-sm font-medium">{p.avg_rating}/5</span>
                  {i === 0 && <DeltaBadge current={p1.avg_rating} previous={p2.avg_rating} />}
                </div>
              </div>
            ))}
          </div>

          {/* Distribution comparison */}
          {distData.length > 0 && (
            <div>
              <div className="text-[10px] text-gray-400 uppercase tracking-wider mb-2">Répartition des notes</div>
              <ResponsiveContainer width="100%" height={180}>
                <BarChart data={distData} margin={{ top: 4, right: 4, bottom: 0, left: -20 }}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                  <XAxis dataKey="rating" tick={{ fontSize: 10, fill: '#9CA3AF' }} axisLine={false} tickLine={false} />
                  <YAxis tick={{ fontSize: 10, fill: '#9CA3AF' }} axisLine={false} tickLine={false} allowDecimals={false} />
                  <Tooltip />
                  <Legend wrapperStyle={{ fontSize: 10 }} iconType="square" iconSize={8} />
                  <Bar dataKey={label1} fill="#000" />
                  <Bar dataKey={label2} fill="#9CA3AF" />
                </BarChart>
              </ResponsiveContainer>
            </div>
          )}

          {/* Source comparison */}
          {(p1.by_source?.length > 0 || p2.by_source?.length > 0) && (
            <div>
              <div className="text-[10px] text-gray-400 uppercase tracking-wider mb-2">Par source</div>
              <div className="grid grid-cols-2 gap-4">
                {[p1, p2].map((p, i) => (
                  <div key={i} className="space-y-1">
                    <div className="text-xs text-gray-400 mb-1">
                      {i === 0 ? label1 : label2}
                    </div>
                    {p.by_source?.map(s => (
                      <div key={s.source} className="flex items-center gap-2 text-xs">
                        <span className="w-2 h-2" style={{ backgroundColor: SOURCE_HEX[s.source] ?? '#9CA3AF' }} />
                        <span className="text-gray-600 flex-1">{SOURCE_LABELS[s.source] ?? s.source}</span>
                        <span className="font-medium">{s.count}</span>
                      </div>
                    ))}
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
