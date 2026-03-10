import { useMemo } from 'react'
import {
  AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer,
  CartesianGrid, Legend, BarChart, Bar,
} from 'recharts'
import { SOURCE_LABELS, SOURCE_HEX } from '../../lib/constants'

function formatDateKey(key, granularity) {
  if (granularity === 'day') {
    const d = new Date(key)
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
  }
  if (granularity === 'week') {
    return key.replace(/^\d{4}-W/, 'S')
  }
  // month: '2024-03' → 'mars 2024'
  const [y, m] = key.split('-')
  return new Date(parseInt(y), parseInt(m) - 1).toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' })
}

function CustomTooltip({ active, payload, label }) {
  if (!active || !payload?.length) return null
  return (
    <div className="bg-white border border-gray-200 px-3 py-2 text-xs shadow-sm">
      <div className="font-medium text-gray-700 mb-1">{label}</div>
      {payload.map((p, i) => (
        <div key={i} className="flex items-center gap-2">
          <span className="w-2 h-2" style={{ backgroundColor: p.color }} />
          <span className="text-gray-500">{p.name}:</span>
          <span className="font-medium">{p.value}</span>
        </div>
      ))}
    </div>
  )
}

export default function TimeSeriesChart({ data, loading }) {
  if (!data || loading) return null

  const { granularity, points } = data

  // Collect all source keys present in data
  const sourceKeys = useMemo(() => {
    const keys = new Set()
    points.forEach(p => {
      Object.keys(p.sources || {}).forEach(k => keys.add(k))
    })
    return Array.from(keys)
  }, [points])

  // Transform for Recharts: flatten sources into top-level keys
  const chartData = useMemo(() =>
    points.map(p => ({
      date: formatDateKey(p.date, granularity),
      Avis: p.count,
      Note: p.avg,
      ...Object.fromEntries(
        sourceKeys.map(k => [SOURCE_LABELS[k] ?? k, p.sources?.[k] ?? 0])
      ),
    })),
    [points, granularity, sourceKeys]
  )

  if (!chartData.length) return <p className="text-xs text-gray-400 italic">Pas de données</p>

  return (
    <div className="space-y-6">
      {/* Review count area chart */}
      <div>
        <div className="text-[10px] text-gray-400 uppercase tracking-wider mb-2">Volume d'avis</div>
        <ResponsiveContainer width="100%" height={180}>
          <AreaChart data={chartData} margin={{ top: 4, right: 4, bottom: 0, left: -20 }}>
            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
            <XAxis
              dataKey="date"
              tick={{ fontSize: 10, fill: '#9CA3AF' }}
              axisLine={{ stroke: '#e5e7eb' }}
              tickLine={false}
            />
            <YAxis
              tick={{ fontSize: 10, fill: '#9CA3AF' }}
              axisLine={false}
              tickLine={false}
              allowDecimals={false}
            />
            <Tooltip content={<CustomTooltip />} />
            <Area
              type="monotone"
              dataKey="Avis"
              stroke="#000"
              fill="#000"
              fillOpacity={0.08}
              strokeWidth={1.5}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* Average rating line */}
      <div>
        <div className="text-[10px] text-gray-400 uppercase tracking-wider mb-2">Note moyenne</div>
        <ResponsiveContainer width="100%" height={140}>
          <AreaChart data={chartData} margin={{ top: 4, right: 4, bottom: 0, left: -20 }}>
            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
            <XAxis
              dataKey="date"
              tick={{ fontSize: 10, fill: '#9CA3AF' }}
              axisLine={{ stroke: '#e5e7eb' }}
              tickLine={false}
            />
            <YAxis
              domain={[0, 5]}
              tick={{ fontSize: 10, fill: '#9CA3AF' }}
              axisLine={false}
              tickLine={false}
              ticks={[1, 2, 3, 4, 5]}
            />
            <Tooltip content={<CustomTooltip />} />
            <Area
              type="monotone"
              dataKey="Note"
              stroke="#374151"
              fill="#374151"
              fillOpacity={0.06}
              strokeWidth={1.5}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* Stacked bar by source */}
      {sourceKeys.length > 1 && (
        <div>
          <div className="text-[10px] text-gray-400 uppercase tracking-wider mb-2">Par source</div>
          <ResponsiveContainer width="100%" height={180}>
            <BarChart data={chartData} margin={{ top: 4, right: 4, bottom: 0, left: -20 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
              <XAxis
                dataKey="date"
                tick={{ fontSize: 10, fill: '#9CA3AF' }}
                axisLine={{ stroke: '#e5e7eb' }}
                tickLine={false}
              />
              <YAxis
                tick={{ fontSize: 10, fill: '#9CA3AF' }}
                axisLine={false}
                tickLine={false}
                allowDecimals={false}
              />
              <Tooltip content={<CustomTooltip />} />
              <Legend
                wrapperStyle={{ fontSize: 10 }}
                iconType="square"
                iconSize={8}
              />
              {sourceKeys.map(k => (
                <Bar
                  key={k}
                  dataKey={SOURCE_LABELS[k] ?? k}
                  stackId="sources"
                  fill={SOURCE_HEX[k] ?? '#9CA3AF'}
                />
              ))}
            </BarChart>
          </ResponsiveContainer>
        </div>
      )}
    </div>
  )
}
