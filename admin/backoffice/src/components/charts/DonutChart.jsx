import { SOURCE_LABELS, SOURCE_HEX } from '../../lib/constants'

export default function DonutChart({ segments }) {
  if (!segments?.length) return null
  const total = segments.reduce((s, d) => s + d.count, 0)
  if (!total) return null

  let offset = 0
  const slices = segments.map(s => {
    const pct = (s.count / total) * 100
    const slice = { ...s, pct, offset, color: SOURCE_HEX[s.source] ?? '#9CA3AF' }
    offset += pct
    return slice
  })

  return (
    <div className="flex items-center gap-4">
      <svg viewBox="0 0 36 36" width="80" height="80" className="shrink-0">
        {slices.map((s, i) => (
          <circle
            key={i}
            cx="18" cy="18" r="15.9155"
            fill="none"
            stroke={s.color}
            strokeWidth="3"
            strokeDasharray={`${s.pct} ${100 - s.pct}`}
            strokeDashoffset={-s.offset}
            className="transition-all duration-500"
          />
        ))}
        <text x="18" y="19" textAnchor="middle" className="text-[7px] fill-gray-700 font-semibold">{total}</text>
        <text x="18" y="23" textAnchor="middle" className="text-[4px] fill-gray-400">avis</text>
      </svg>
      <div className="flex flex-col gap-1 flex-1 min-w-0">
        {slices.map((s, i) => (
          <div key={i} className="flex items-center gap-2 text-xs">
            <span className="w-2 h-2 shrink-0" style={{ backgroundColor: s.color }} />
            <span className="text-gray-600 truncate flex-1">{SOURCE_LABELS[s.source] ?? s.source}</span>
            <span className="font-medium text-gray-800">{s.count}</span>
            <span className="text-gray-400 w-8 text-right">{Math.round(s.pct)}%</span>
          </div>
        ))}
      </div>
    </div>
  )
}
