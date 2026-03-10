import { useNavigate } from 'react-router-dom'
import { useDashboard } from '../hooks/useDashboard'
import { useToast } from '../components/Toast'
import { api } from '../lib/api'
import { PageHeader, StatCard, Table, Btn, Spinner, Stars, Badge, RatingBar } from '../components/ui'
import { IconPlus } from '../components/Icons'
import { SOURCE_LABELS, SOURCE_COLORS } from '../lib/constants'
import FilterBar from '../components/FilterBar'
import ChartCard from '../components/charts/ChartCard'
import MiniBarChart from '../components/charts/MiniBarChart'
import DonutChart from '../components/charts/DonutChart'
import TimeSeriesChart from '../components/charts/TimeSeriesChart'
import SeasonCompare from '../components/charts/SeasonCompare'

export default function Dashboard() {
  const navigate = useNavigate()
  const toast = useToast()
  const {
    data, loading, period, setPeriod,
    sourceFilter, setSourceFilter, lieuFilter, setLieuFilter,
    fromDate, setFromDate, toDate, setToDate,
    lieux, activeLieux, monthlyTrend,
    trends, trendsLoading,
    comparison, comparisonLoading, compareSeason, compareRange,
  } = useDashboard()

  const totalFromSources = data?.by_source?.reduce((sum, s) => sum + (s.count ?? 0), 0)
  const totalForDisplay = totalFromSources != null && data?.by_source?.length ? totalFromSources : (data?.total ?? 0)

  const recentCols = [
    {
      key: 'author', label: 'Auteur',
      render: r => (
        <div className="flex items-center gap-2">
          {r.avatar
            ? <img src={r.avatar} alt="" className="w-6 h-6 rounded-full object-cover" />
            : <div className="w-6 h-6 rounded-full bg-secondary flex items-center justify-center text-xs text-muted-foreground">{r.author?.[0]}</div>
          }
          <span>{r.author}</span>
          {r.certified && <Badge variant="certified">Certifié</Badge>}
        </div>
      ),
    },
    { key: 'rating',   label: 'Note',   render: r => <Stars rating={r.rating} size={12} /> },
    { key: 'source',   label: 'Source', render: r => <Badge variant={r.source}>{SOURCE_LABELS[r.source] ?? r.source}</Badge> },
    { key: 'date_rel', label: 'Date',   render: r => <span className="text-muted-foreground">{r.date_rel}</span> },
    {
      key: 'actions', label: '',
      render: r => (
        <button onClick={() => navigate(`/reviews/${r.id}`)} className="text-xs text-muted-foreground hover:text-foreground underline">Modifier</button>
      ),
    },
  ]

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

      {loading && !data ? (
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      ) : (
        <>
          {/* Filters: period + source + lieu */}
          <FilterBar
            period={period} setPeriod={setPeriod}
            sourceFilter={sourceFilter} setSourceFilter={setSourceFilter}
            lieuFilter={lieuFilter} setLieuFilter={setLieuFilter}
            fromDate={fromDate} setFromDate={setFromDate}
            toDate={toDate} setToDate={setToDate}
            lieux={lieux}
          />

          {/* Stats — responsive 2→4 columns */}
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-px bg-border mx-6 mt-4 border border-border rounded-lg overflow-hidden">
            <StatCard
              label="Avis Globaux"
              value={totalForDisplay}
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
                  ? <span>{data.google_total} <span className="text-sm font-normal text-muted-foreground">avis</span></span>
                  : '—'
              }
              sub={
                data?.google_avg
                  ? <Stars rating={data.google_avg} size={12} />
                  : <span className="text-xs text-muted-foreground">Aucun avis Google</span>
              }
            />
            <StatCard
              label="Répartition"
              value={
                <div className="flex flex-col gap-1 mt-2">
                  {[5, 4, 3, 2, 1].map(n => (
                    <RatingBar key={n} value={n} count={data?.distribution?.[n] ?? 0} max={totalForDisplay || 1} />
                  ))}
                </div>
              }
            />
          </div>

          {/* Time-series trends */}
          <div className="mx-6 mt-6">
            <ChartCard title="Tendances" loading={trendsLoading}>
              <TimeSeriesChart data={trends} loading={trendsLoading} totalVisible={data?.total} />
            </ChartCard>
          </div>

          {/* Monthly trend (mini bar) */}
          {monthlyTrend.length > 0 && (
            <div className="mx-6 mt-6">
              <ChartCard title="Évolution mensuelle (6 derniers mois)">
                <MiniBarChart data={monthlyTrend} />
              </ChartCard>
            </div>
          )}

          {/* Row 2: Source donut + Lieux overview */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mx-6 mt-6">
            <ChartCard title="Répartition par source">
              <DonutChart segments={data?.by_source?.map(s => ({ source: s.source, count: s.count })) ?? []} />
            </ChartCard>

            <div className="rounded-lg border bg-card p-5">
              <div className="flex items-center justify-between mb-4">
                <span className="text-xs text-muted-foreground uppercase tracking-wider">Lieux actifs</span>
                <button onClick={() => navigate('/lieux')} className="text-xs text-muted-foreground underline hover:text-foreground">Gérer</button>
              </div>
              {activeLieux.length === 0 ? (
                <p className="text-xs text-muted-foreground italic">Aucun lieu actif.</p>
              ) : (
                <div className="flex flex-col gap-2">
                  {activeLieux.slice(0, 5).map(l => {
                    const platformCount = l.reviews_count ?? 0
                    const cptCount = l.avis_count ?? 0
                    const displayCount = Math.max(platformCount, cptCount)
                    return (
                      <div key={l.id} className="flex items-center gap-3 text-sm">
                        <span className={`w-2 h-2 shrink-0 rounded-full ${SOURCE_COLORS[l.source] ?? 'bg-muted-foreground'}`} />
                        <span className="truncate flex-1">{l.name}</span>
                        {l.rating > 0 && (
                          <span className="text-xs text-muted-foreground">
                            {Number(l.rating).toFixed(1)}
                          </span>
                        )}
                        <span className="text-xs text-muted-foreground">{displayCount} avis</span>
                      </div>
                    )
                  })}
                  {activeLieux.length > 5 && (
                    <button onClick={() => navigate('/lieux')} className="text-xs text-muted-foreground underline hover:text-foreground">
                      +{activeLieux.length - 5} autres
                    </button>
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Season comparison */}
          <div className="mx-6 mt-6">
            <ChartCard title="Comparateur de périodes">
              <SeasonCompare
                comparison={comparison}
                loading={comparisonLoading}
                onCompare={compareSeason}
                onCompareRange={compareRange}
              />
            </ChartCard>
          </div>

          {/* Per platform */}
          {data?.by_source?.length > 0 && (
            <div className="mx-6 mt-6">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs text-muted-foreground uppercase tracking-wider">Par plateforme</span>
              </div>
              <div className="rounded-lg border divide-y">
                {data.by_source.map(({ source, count, avg_rating }) => (
                  <button
                    key={source}
                    onClick={() => navigate(`/reviews?source=${source}`)}
                    className="w-full flex items-center gap-4 px-4 py-3 hover:bg-muted/50 transition-colors text-left group"
                  >
                    <div className={`w-2.5 h-2.5 shrink-0 rounded-full ${SOURCE_COLORS[source] ?? 'bg-muted-foreground'}`} />
                    <span className="text-sm group-hover:text-foreground w-28">
                      {SOURCE_LABELS[source] ?? source}
                    </span>
                    <span className="text-xs font-medium bg-secondary px-2 py-0.5 rounded-md min-w-8 text-center">
                      {count}
                    </span>
                    {avg_rating > 0 && (
                      <div className="flex items-center gap-1.5 ml-2">
                        <Stars rating={avg_rating} size={11} />
                        <span className="text-xs text-muted-foreground">{avg_rating.toFixed(1)}</span>
                      </div>
                    )}
                    <span className="ml-auto text-xs text-muted-foreground/50 group-hover:text-muted-foreground">→</span>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Recent reviews */}
          <div className="mx-6 mt-6 mb-10">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs text-muted-foreground uppercase tracking-wider">Avis récents</span>
              <button onClick={() => navigate('/reviews')} className="text-xs text-muted-foreground underline hover:text-foreground">
                Tout voir
              </button>
            </div>
            <div className="rounded-lg border">
              <Table columns={recentCols} data={data?.recent ?? []} empty="Aucun avis pour le moment." />
            </div>
          </div>
        </>
      )}
    </div>
  )
}
