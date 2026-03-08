import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import {
  PageHeader, Table, Btn, Notice, Spinner, Stars, Badge, Pagination, Input, Select
} from '../components/ui'
import { Plus, Trash, EditPencil, ArrowUp, ArrowDown } from 'iconoir-react'

const SOURCE_LABELS = {
  google: 'Google', tripadvisor: 'TripAdvisor', facebook: 'Facebook',
  trustpilot: 'Trustpilot', direct: 'Direct', autre: 'Autre',
}

const SOURCE_OPTIONS = [
  { value: '', label: 'Toutes les sources' },
  ...Object.entries(SOURCE_LABELS).map(([v, l]) => ({ value: v, label: l })),
]

export default function Reviews() {
  const navigate = useNavigate()
  const [items, setItems]         = useState([])
  const [total, setTotal]         = useState(0)
  const [page, setPage]           = useState(1)
  const [loading, setLoading]     = useState(true)
  const [error, setError]         = useState(null)
  const [search, setSearch]       = useState('')
  const [ratingFilter, setRating] = useState(0)
  const [sourceFilter, setSource] = useState('')
  const [lieuFilter, setLieu]     = useState('')
  const [lieux, setLieux]         = useState([])
  const [orderby, setOrderby]     = useState('date')
  const [order, setOrder]         = useState('DESC')
  const [deleting, setDeleting]   = useState(null)
  const PER_PAGE = 20

  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.reviews({
        page, perPage: PER_PAGE,
        search, rating: ratingFilter,
        source: sourceFilter, lieu_id: lieuFilter,
        orderby, order,
      })
      setItems(res.items)
      setTotal(res.total)
    } catch (e) {
      setError(e.message)
    } finally {
      setLoading(false)
    }
  }, [page, search, ratingFilter, sourceFilter, lieuFilter, orderby, order])

  useEffect(() => { load() }, [load])

  const resetFilters = () => {
    setSearch(''); setRating(0); setSource(''); setLieu(''); setPage(1)
  }
  const hasFilters = search || ratingFilter > 0 || sourceFilter || lieuFilter

  const toggleSort = (col) => {
    if (orderby === col) {
      setOrder(o => o === 'DESC' ? 'ASC' : 'DESC')
    } else {
      setOrderby(col)
      setOrder('DESC')
    }
    setPage(1)
  }

  const SortIcon = ({ col }) => {
    if (orderby !== col) return null
    return order === 'DESC'
      ? <ArrowDown className="w-3 h-3 inline ml-1 text-gray-400" />
      : <ArrowUp className="w-3 h-3 inline ml-1 text-gray-400" />
  }

  async function handleDelete(id) {
    if (!confirm('Supprimer cet avis ?')) return
    setDeleting(id)
    try {
      await api.deleteReview(id)
      await load()
    } catch (e) {
      setError(e.message)
    } finally {
      setDeleting(null)
    }
  }

  const lieuName = (lieu_id) => lieux.find(l => l.id === lieu_id)?.name ?? null

  const columns = [
    {
      key: 'author', label: (
        <button onClick={() => toggleSort('author')} className="flex items-center gap-1 hover:text-black">
          Auteur <SortIcon col="author" />
        </button>
      ),
      render: r => (
        <div className="flex items-center gap-2">
          {r.avatar
            ? <img src={r.avatar} alt="" className="w-7 h-7 rounded-full object-cover shrink-0" />
            : <div className="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium shrink-0">
                {r.author?.[0]?.toUpperCase()}
              </div>
          }
          <div>
            <div className="font-medium text-sm">{r.author}</div>
            <div className="flex items-center gap-1 flex-wrap mt-0.5">
              {r.certified && <Badge variant="certified">Certifié</Badge>}
              {r.lieu_id && lieuName(r.lieu_id) && (
                <Badge variant="default">{lieuName(r.lieu_id)}</Badge>
              )}
            </div>
          </div>
        </div>
      ),
    },
    {
      key: 'rating', label: (
        <button onClick={() => toggleSort('rating')} className="flex items-center gap-1 hover:text-black">
          Note <SortIcon col="rating" />
        </button>
      ),
      render: r => <Stars rating={r.rating} size={13} />,
    },
    {
      key: 'text', label: 'Avis',
      render: r => (
        <span className="text-gray-500 line-clamp-2 max-w-xs text-sm">
          {r.text || <em className="text-gray-300">—</em>}
        </span>
      ),
    },
    {
      key: 'source', label: 'Source',
      render: r => <span className="text-xs text-gray-500">{SOURCE_LABELS[r.source] ?? r.source}</span>,
    },
    {
      key: 'date_rel', label: (
        <button onClick={() => toggleSort('date')} className="flex items-center gap-1 hover:text-black">
          Date <SortIcon col="date" />
        </button>
      ),
      render: r => <span className="text-xs text-gray-400">{r.date_rel}</span>,
    },
    {
      key: 'actions', label: '',
      render: r => (
        <div className="flex items-center gap-1">
          <Btn size="sm" variant="ghost" onClick={() => navigate(`/reviews/${r.id}`)}>
            <EditPencil width={13} height={13} />
          </Btn>
          <Btn size="sm" variant="danger" loading={deleting === r.id} onClick={() => handleDelete(r.id)}>
            <Trash width={13} height={13} />
          </Btn>
        </div>
      ),
    },
  ]

  return (
    <div>
      <PageHeader
        title="Tous les avis"
        actions={
          <Btn size="sm" onClick={() => navigate('/reviews/new')}>
            <Plus width={13} height={13} />
            Ajouter
          </Btn>
        }
      />

      {/* Filtres */}
      <div className="flex flex-wrap items-center gap-2 px-8 py-4 border-b border-gray-100">
        <div className="w-52">
          <Input
            placeholder="Rechercher…"
            value={search}
            onChange={e => { setSearch(e.target.value); setPage(1) }}
          />
        </div>
        <div className="w-36">
          <Select
            value={ratingFilter}
            onChange={e => { setRating(Number(e.target.value)); setPage(1) }}
          >
            <option value={0}>Toutes les notes</option>
            {[5, 4, 3, 2, 1].map(n => (
              <option key={n} value={n}>{n} étoile{n > 1 ? 's' : ''}</option>
            ))}
          </Select>
        </div>
        <div className="w-40">
          <Select
            value={sourceFilter}
            onChange={e => { setSource(e.target.value); setPage(1) }}
          >
            {SOURCE_OPTIONS.map(o => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </Select>
        </div>
        {lieux.length > 0 && (
          <div className="w-44">
            <Select
              value={lieuFilter}
              onChange={e => { setLieu(e.target.value); setPage(1) }}
            >
              <option value="">Tous les lieux</option>
              {lieux.map(l => (
                <option key={l.id} value={l.id}>{l.name}</option>
              ))}
            </Select>
          </div>
        )}
        {hasFilters && (
          <Btn size="sm" variant="ghost" onClick={resetFilters}>Réinitialiser</Btn>
        )}
        <span className="ml-auto text-xs text-gray-400">{total} avis</span>
      </div>

      {error && <div className="px-8 pt-4"><Notice type="error">{error}</Notice></div>}

      <div className="border-t border-gray-100">
        <Table columns={columns} data={items} loading={loading} empty="Aucun avis trouvé." />
        <Pagination page={page} total={total} perPage={PER_PAGE} onChange={setPage} />
      </div>
    </div>
  )
}
