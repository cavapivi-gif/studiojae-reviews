import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, StatCard, Table, Btn, Notice, Spinner, Stars, Badge, RatingBar } from '../components/ui'
import { IconPlus } from '../components/Icons'
import { SOURCE_LABELS, SOURCE_COLORS } from '../lib/constants'

export default function Dashboard() {
  const navigate = useNavigate()
  const [data, setData]       = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError]     = useState(null)

  useEffect(() => {
    api.dashboard()
      .then(setData)
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

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
    { key: 'source',   label: 'Source',  render: r => <span className="text-gray-500">{SOURCE_LABELS[r.source] ?? r.source}</span> },
    { key: 'date_rel', label: 'Date',    render: r => <span className="text-gray-400">{r.date_rel}</span> },
    {
      key: 'actions', label: '',
      render: r => (
        <button
          onClick={() => navigate(`/reviews/${r.id}`)}
          className="text-xs text-gray-400 hover:text-black underline"
        >
          Modifier
        </button>
      ),
    },
  ]

  return (
    <div>
      <PageHeader
        title="Tableau de bord"
        actions={
          <Btn size="sm" onClick={() => navigate('/reviews/new')}>
            <IconPlus width={13} height={13} />
            Ajouter un avis
          </Btn>
        }
      />

      {error && <div className="px-8 pt-6"><Notice type="error">{error}</Notice></div>}

      {loading ? (
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      ) : (
        <>
          {/* Stats — 4 colonnes */}
          <div className="grid grid-cols-4 gap-px bg-gray-200 mx-8 mt-8 border border-gray-200">
            <StatCard
              label="Avis publiés"
              value={data?.total ?? 0}
              sub={<button onClick={() => navigate('/reviews')} className="text-xs underline">Voir tout</button>}
            />
            <StatCard
              label="Note moyenne"
              value={data?.avg_rating ? `${data.avg_rating} / 5` : '—'}
              accent
              sub={<Stars rating={Math.round(data?.avg_rating ?? 0)} size={12} />}
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
                  ? <Stars rating={Math.round(data.google_avg)} size={12} />
                  : <span className="text-xs text-gray-400">Aucun avis Google</span>
              }
            />
            <StatCard
              label="Répartition"
              value={
                <div className="flex flex-col gap-1 mt-2">
                  {[5, 4, 3, 2, 1].map(n => (
                    <RatingBar
                      key={n}
                      value={n}
                      count={data?.distribution?.[n] ?? 0}
                      max={data?.total ?? 1}
                    />
                  ))}
                </div>
              }
            />
          </div>

          {/* Par plateforme */}
          {data?.by_source?.length > 0 && (
            <div className="mx-8 mt-8">
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
                    <div className={`w-2.5 h-2.5 rounded-full shrink-0 ${SOURCE_COLORS[source] ?? 'bg-gray-400'}`} />
                    <span className="text-sm font-medium text-gray-700 group-hover:text-black w-28">
                      {SOURCE_LABELS[source] ?? source}
                    </span>
                    <span className="text-xs font-semibold text-gray-900 bg-gray-100 rounded px-2 py-0.5 min-w-8 text-center">
                      {count}
                    </span>
                    {avg_rating > 0 && (
                      <div className="flex items-center gap-1.5 ml-2">
                        <Stars rating={Math.round(avg_rating)} size={11} />
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
          <div className="mx-8 mt-8 mb-10">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs text-gray-400 uppercase tracking-widest">Avis récents</span>
              <button onClick={() => navigate('/reviews')} className="text-xs text-gray-400 underline hover:text-black">
                Tout voir
              </button>
            </div>
            <div className="border border-gray-200">
              <Table
                columns={recentCols}
                data={data?.recent ?? []}
                empty="Aucun avis pour le moment."
              />
            </div>
          </div>
        </>
      )}
    </div>
  )
}
