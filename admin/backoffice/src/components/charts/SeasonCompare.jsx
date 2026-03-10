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

export default function SeasonCompare({ comparison, loading, onCompare }) {
  const [season1, setSeason1] = useState('summer')
  const [year1, setYear1] = useState(currentYear)
  const [season2, setSeason2] = useState('summer')
  const [year2, setYear2] = useState(currentYear - 1)

  const handleCompare = () => {
    onCompare(season1, year1, season2, year2)
  }

  const periods = comparison?.periods
  const p1 = periods?.[0]
  const p2 = periods?.[1]

  // Distribution comparison chart data
  const distData = useMemo(() => {
    if (!p1 || !p2) return []
    return [5, 4, 3, 2, 1].map(n => ({
      rating: `${n}★`,
      [`${SEASONS[p1.season]?.label ?? p1.season} ${p1.year}`]: p1.distribution?.[n] ?? 0,
      [`${SEASONS[p2.season]?.label ?? p2.season} ${p2.year}`]: p2.distribution?.[n] ?? 0,
    }))
  }, [p1, p2])

  return (
    <div>
      {/* Selectors */}
      <div className="flex items-center gap-6 flex-wrap">
        <SeasonSelector label="Période A" season={season1} year={year1} onSeasonChange={setSeason1} onYearChange={setYear1} />
        <span className="text-gray-300 text-xs">vs</span>
        <SeasonSelector label="Période B" season={season2} year={year2} onSeasonChange={setSeason2} onYearChange={setYear2} />
        <button
          onClick={handleCompare}
          className="px-3 py-1.5 text-xs font-medium bg-black text-white hover:bg-gray-800 transition-colors"
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
                  {SEASONS[p.season]?.label ?? p.season} {p.year}
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
                  <Bar dataKey={`${SEASONS[p1.season]?.label ?? p1.season} ${p1.year}`} fill="#000" />
                  <Bar dataKey={`${SEASONS[p2.season]?.label ?? p2.season} ${p2.year}`} fill="#9CA3AF" />
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
                      {SEASONS[p.season]?.label ?? p.season} {p.year}
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
