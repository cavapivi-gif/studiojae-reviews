import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, StatCard, Table, Btn, Notice, Spinner, Stars, Badge, RatingBar } from '../components/ui'
import { Plus, MapPin } from 'iconoir-react'

const SOURCE_LABELS = {
  google: 'Google', tripadvisor: 'TripAdvisor', facebook: 'Facebook',
  trustpilot: 'Trustpilot', direct: 'Direct', autre: 'Autre',
}

const SOURCE_COLORS = {
  google:      'bg-blue-500',
  tripadvisor: 'bg-emerald-500',
  facebook:    'bg-blue-700',
  trustpilot:  'bg-green-600',
  direct:      'bg-gray-600',
  autre:       'bg-gray-400',
}

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
            <Plus width={13} height={13} />
            Ajouter un avis
          </Btn>
        }
      />

      {error && <div className="px-8 pt-6"><Notice type="error">{error}</Notice></div>}

      {loading ? (
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      ) : (
        <>
          {/* Stats */}
          <div className="grid grid-cols-3 gap-px bg-gray-200 mx-8 mt-8 border border-gray-200">
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

          {/* Par source */}
          {data?.by_source?.length > 0 && (
            <div className="mx-8 mt-8">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs text-gray-400 uppercase tracking-widest">Par plateforme</span>
              </div>
              <div className="border border-gray-200 p-4 flex flex-wrap gap-4">
                {data.by_source.map(({ source, count }) => (
                  <button
                    key={source}
                    onClick={() => navigate(`/reviews?source=${source}`)}
                    className="flex items-center gap-2 group"
                    title={`Filtrer par ${SOURCE_LABELS[source] ?? source}`}
                  >
                    <div className={`w-2 h-2 rounded-full shrink-0 ${SOURCE_COLORS[source] ?? 'bg-gray-400'}`} />
                    <span className="text-sm text-gray-700 group-hover:text-black">
                      {SOURCE_LABELS[source] ?? source}
                    </span>
                    <span className="text-xs font-semibold text-gray-900 bg-gray-100 rounded px-1.5 py-0.5">
                      {count}
                    </span>
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
