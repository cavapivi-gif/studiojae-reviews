import { useState, useEffect, useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, StatCard, Table, Btn, Spinner, Stars, Badge, RatingBar } from '../components/ui'
import { IconPlus } from '../components/Icons'
import { SOURCE_LABELS, SOURCE_COLORS } from '../lib/constants'
import { useToast } from '../components/Toast'

/* ── Mini bar chart (pure CSS) ────────────────────────── */
function MiniBarChart({ data, label = 'avis' }) {
  if (!data?.length) return <p className="text-xs text-gray-400 italic">Pas de données</p>
  const max = Math.max(...data.map(d => d.count), 1)
  return (
    <div className="flex items-end gap-[3px] h-24">
      {data.map((d, i) => (
        <div key={i} className="flex-1 flex flex-col items-center gap-1 min-w-0">
          <span className="text-[9px] text-gray-400 leading-none">{d.count || ''}</span>
          <div
            className="w-full bg-black transition-all duration-300 min-h-[2px]"
            style={{ height: `${Math.max((d.count / max) * 100, 2)}%` }}
            title={`${d.label}: ${d.count} ${label}`}
          />
          <span className="text-[9px] text-gray-400 leading-none truncate w-full text-center">{d.label}</span>
        </div>
      ))}
    </div>
  )
}

/* ── Donut chart (SVG) ────────────────────────────────── */
function DonutChart({ segments }) {
  if (!segments?.length) return null
  const total = segments.reduce((s, d) => s + d.count, 0)
  if (!total) return null

  const colors = {
    google: '#4285F4', tripadvisor: '#00AF87', facebook: '#1877F2',
    trustpilot: '#00B67A', regiondo: '#e85c2c', direct: '#374151', autre: '#9CA3AF',
  }

  let offset = 0
  const slices = segments.map(s => {
    const pct = (s.count / total) * 100
    const slice = { ...s, pct, offset, color: colors[s.source] ?? '#9CA3AF' }
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

/* ── Period filter ────────────────────────────────────── */
const PERIODS = [
  { value: '7d', label: '7 jours' },
  { value: '30d', label: '30 jours' },
  { value: '90d', label: '90 jours' },
  { value: '12m', label: '12 mois' },
  { value: 'all', label: 'Tout' },
]

export default function Dashboard() {
  const navigate = useNavigate()
  const toast = useToast()
  const [data, setData]       = useState(null)
  const [loading, setLoading] = useState(true)
  const [period, setPeriod]   = useState('all')
  const [lieux, setLieux]     = useState([])

  useEffect(() => {
    Promise.all([api.dashboard(), api.lieux()])
      .then(([d, l]) => { setData(d); setLieux(l) })
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [])

  // Monthly trend from recent reviews (simulate from data we have)
  const monthlyTrend = useMemo(() => {
    if (!data?.recent) return []
    // Build last 6 months labels
    const months = []
    const now = new Date()
    for (let i = 5; i >= 0; i--) {
      const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
      months.push({
        label: d.toLocaleDateString('fr-FR', { month: 'short' }),
        year: d.getFullYear(),
        month: d.getMonth(),
        count: 0,
      })
    }
    return months
  }, [data])

  const recentCols = [
    {
      key: 'author', label: 'Auteur',
      render: r => (
        <div className="flex items-center gap-2">
          {r.avatar
            ? <img src={r.avatar} alt="" className="w-6 h-6 rounded-full object-cover" />
            : <div className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs">{r.author?.[0]}</div>
          }
          <span>{r.author}</span>
          {r.certified && <Badge variant="certified">Certifié</Badge>}
        </div>
      ),
    },
    { key: 'rating',   label: 'Note',    render: r => <Stars rating={r.rating} size={12} /> },
    { key: 'source',   label: 'Source',  render: r => <Badge variant={r.source}>{SOURCE_LABELS[r.source] ?? r.source}</Badge> },
    { key: 'date_rel', label: 'Date',    render: r => <span className="text-gray-400">{r.date_rel}</span> },
    {
      key: 'actions', label: '',
      render: r => (
        <button onClick={() => navigate(`/reviews/${r.id}`)} className="text-xs text-gray-400 hover:text-black underline">Modifier</button>
      ),
    },
  ]

  // Export handler
  const handleExport = async () => {
    try {
      const res = await api.exportCsv({})
      const blob = new Blob([res.csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = res.filename
      a.click()
      URL.revokeObjectURL(url)
      toast.success(`${res.count} avis exportés.`)
    } catch (e) {
      toast.error(e.message)
    }
  }

  return (
    <div>
      <PageHeader
        title="Tableau de bord"
        actions={
          <div className="flex items-center gap-2">
            <Btn variant="secondary" size="sm" onClick={handleExport}>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              Export CSV
            </Btn>
            <Btn size="sm" onClick={() => navigate('/reviews/new')}>
              <IconPlus width={13} height={13} />
              Ajouter un avis
            </Btn>
          </div>
        }
      />

      {loading ? (
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      ) : (
        <>
          {/* Period filter */}
          <div className="px-8 mt-6 flex items-center gap-1">
            {PERIODS.map(p => (
              <button
                key={p.value}
                onClick={() => setPeriod(p.value)}
                className={`px-3 py-1.5 text-xs font-medium transition-colors
                  ${period === p.value ? 'bg-black text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`}
              >
                {p.label}
              </button>
            ))}
          </div>

          {/* Stats — 4 colonnes */}
          <div className="grid grid-cols-4 gap-px bg-gray-200 mx-8 mt-4 border border-gray-200">
            <StatCard
              label="Avis publiés"
              value={data?.total ?? 0}
              sub={<button onClick={() => navigate('/reviews')} className="text-xs underline">Voir tout</button>}
            />
            <StatCard
              label="Note moyenne"
              value={data?.avg_rating ? `${data.avg_rating} / 5` : '—'}
              accent
              sub={<Stars rating={data?.avg_rating ?? 0} size={12} />}
            />
            <StatCard
              label="Google"
              value={
                data?.google_total
                  ? <span>{data.google_total} <span className="text-sm font-normal text-gray-400">avis</span></span>
                  : '—'
              }
              sub={
                data?.google_avg
                  ? <Stars rating={data.google_avg} size={12} />
                  : <span className="text-xs text-gray-400">Aucun avis Google</span>
              }
            />
            <StatCard
              label="Répartition"
              value={
                <div className="flex flex-col gap-1 mt-2">
                  {[5, 4, 3, 2, 1].map(n => (
                    <RatingBar key={n} value={n} count={data?.distribution?.[n] ?? 0} max={data?.total ?? 1} />
                  ))}
                </div>
              }
            />
          </div>

          {/* Row 2: Source donut + Lieux overview */}
          <div className="grid grid-cols-2 gap-6 mx-8 mt-6">
            {/* Sources donut */}
            <div className="border border-gray-200 p-5">
              <div className="text-xs text-gray-400 uppercase tracking-widest mb-4">Répartition par source</div>
              <DonutChart segments={data?.by_source?.map(s => ({ source: s.source, count: s.count })) ?? []} />
            </div>

            {/* Lieux summary */}
            <div className="border border-gray-200 p-5">
              <div className="flex items-center justify-between mb-4">
                <span className="text-xs text-gray-400 uppercase tracking-widest">Lieux actifs</span>
                <button onClick={() => navigate('/lieux')} className="text-xs text-gray-400 underline hover:text-black">Gérer</button>
              </div>
              {lieux.filter(l => l.active).length === 0 ? (
                <p className="text-xs text-gray-400 italic">Aucun lieu actif.</p>
              ) : (
                <div className="flex flex-col gap-2">
                  {lieux.filter(l => l.active).slice(0, 5).map(l => (
                    <div key={l.id} className="flex items-center gap-3 text-sm">
                      <span className={`w-2 h-2 shrink-0 ${SOURCE_COLORS[l.source] ?? 'bg-gray-400'}`} />
                      <span className="text-gray-700 truncate flex-1">{l.name}</span>
                      {l.rating > 0 && (
                        <span className="text-xs font-medium text-gray-500">
                          {Number(l.rating).toFixed(1)} ({l.reviews_count})
                        </span>
                      )}
                      <span className="text-xs text-gray-400">{l.avis_count ?? 0} avis</span>
                    </div>
                  ))}
                  {lieux.filter(l => l.active).length > 5 && (
                    <button onClick={() => navigate('/lieux')} className="text-xs text-gray-400 underline">
                      +{lieux.filter(l => l.active).length - 5} autres
                    </button>
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Per platform — clickable rows with avg */}
          {data?.by_source?.length > 0 && (
            <div className="mx-8 mt-6">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs text-gray-400 uppercase tracking-widest">Par plateforme</span>
              </div>
              <div className="border border-gray-200 divide-y">
                {data.by_source.map(({ source, count, avg_rating }) => (
                  <button
                    key={source}
                    onClick={() => navigate(`/reviews?source=${source}`)}
                    className="w-full flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition-colors text-left group"
                  >
                    <div className={`w-2.5 h-2.5 shrink-0 ${SOURCE_COLORS[source] ?? 'bg-gray-400'}`} />
                    <span className="text-sm font-medium text-gray-700 group-hover:text-black w-28">
                      {SOURCE_LABELS[source] ?? source}
                    </span>
                    <span className="text-xs font-semibold text-gray-900 bg-gray-100 px-2 py-0.5 min-w-8 text-center">
                      {count}
                    </span>
                    {avg_rating > 0 && (
                      <div className="flex items-center gap-1.5 ml-2">
                        <Stars rating={avg_rating} size={11} />
                        <span className="text-xs text-gray-500">{avg_rating.toFixed(1)}</span>
                      </div>
                    )}
                    <span className="ml-auto text-xs text-gray-300 group-hover:text-gray-500">→</span>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Avis récents */}
          <div className="mx-8 mt-6 mb-10">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs text-gray-400 uppercase tracking-widest">Avis récents</span>
              <button onClick={() => navigate('/reviews')} className="text-xs text-gray-400 underline hover:text-black">
                Tout voir
              </button>
            </div>
            <div className="border border-gray-200">
              <Table columns={recentCols} data={data?.recent ?? []} empty="Aucun avis pour le moment." />
            </div>
          </div>
        </>
      )}
    </div>
  )
}
