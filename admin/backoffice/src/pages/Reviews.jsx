import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import {
  PageHeader, Table, Btn, Notice, Spinner, Stars, Badge, Pagination, Input, Select
} from '../components/ui'
import { Plus, Trash, EditPencil } from 'iconoir-react'

const SOURCE_LABELS = {
  google: 'Google', tripadvisor: 'TripAdvisor', facebook: 'Facebook',
  trustpilot: 'Trustpilot', direct: 'Direct', autre: 'Autre',
}

export default function Reviews() {
  const navigate = useNavigate()
  const [items, setItems]       = useState([])
  const [total, setTotal]       = useState(0)
  const [page, setPage]         = useState(1)
  const [loading, setLoading]   = useState(true)
  const [error, setError]       = useState(null)
  const [search, setSearch]     = useState('')
  const [ratingFilter, setRating] = useState(0)
  const [deleting, setDeleting] = useState(null)
  const PER_PAGE = 20

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.reviews({ page, perPage: PER_PAGE, search, rating: ratingFilter })
      setItems(res.items)
      setTotal(res.total)
    } catch (e) {
      setError(e.message)
    } finally {
      setLoading(false)
    }
  }, [page, search, ratingFilter])

  useEffect(() => { load() }, [load])

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

  const columns = [
    {
      key: 'author', label: 'Auteur',
      render: r => (
        <div className="flex items-center gap-2">
          {r.avatar
            ? <img src={r.avatar} alt="" className="w-7 h-7 rounded-full object-cover" />
            : <div className="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium">
                {r.author?.[0]?.toUpperCase()}
              </div>
          }
          <div>
            <div className="font-medium">{r.author}</div>
            {r.certified && <Badge variant="certified">Certifié</Badge>}
          </div>
        </div>
      ),
    },
    { key: 'rating', label: 'Note', render: r => <Stars rating={r.rating} size={13} /> },
    {
      key: 'text', label: 'Avis',
      render: r => (
        <span className="text-gray-500 line-clamp-2 max-w-xs">
          {r.text || <em className="text-gray-300">—</em>}
        </span>
      ),
    },
    {
      key: 'source', label: 'Source',
      render: r => <span className="text-xs text-gray-500">{SOURCE_LABELS[r.source] ?? r.source}</span>,
    },
    { key: 'date_rel', label: 'Date', render: r => <span className="text-xs text-gray-400">{r.date_rel}</span> },
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
      <div className="flex items-center gap-3 px-8 py-4 border-b border-gray-100">
        <div className="w-64">
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
        {(search || ratingFilter > 0) && (
          <Btn size="sm" variant="ghost" onClick={() => { setSearch(''); setRating(0); setPage(1) }}>
            Réinitialiser
          </Btn>
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
