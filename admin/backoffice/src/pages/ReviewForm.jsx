import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, Btn, Input, Textarea, Select, Toggle, Notice, Spinner, Stars, StarPicker } from '../components/ui'
import { Check, ArrowLeft, Trash2 } from 'lucide-react'

const SOURCES = [
  { value: 'google',      label: 'Google' },
  { value: 'tripadvisor', label: 'TripAdvisor' },
  { value: 'facebook',    label: 'Facebook' },
  { value: 'trustpilot',  label: 'Trustpilot' },
  { value: 'direct',      label: 'Direct' },
  { value: 'autre',       label: 'Autre' },
]

const EMPTY = { author: '', rating: 5, text: '', certified: false, source: 'google', place_id: '', lieu_id: '' }

export default function ReviewForm() {
  const { id }    = useParams()
  const navigate  = useNavigate()
  const isEdit    = Boolean(id)

  const [form, setForm]       = useState(EMPTY)
  const [loading, setLoading] = useState(isEdit)
  const [saving, setSaving]   = useState(false)
  const [deleting, setDel]    = useState(false)
  const [error, setError]     = useState(null)
  const [saved, setSaved]     = useState(false)
  const [lieux, setLieux]     = useState([])

  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  useEffect(() => {
    if (!isEdit) return
    api.review(id)
      .then(r => setForm({
        author:    r.author ?? '',
        rating:    r.rating ?? 5,
        text:      r.text ?? '',
        certified: r.certified ?? false,
        source:    r.source ?? 'google',
        place_id:  r.place_id ?? '',
        lieu_id:   r.lieu_id ?? '',
      }))
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [id, isEdit])

  function set(key) {
    return value => setForm(f => ({ ...f, [key]: value }))
  }

  async function handleSubmit(e) {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setSaved(false)
    try {
      if (isEdit) {
        await api.updateReview(id, form)
      } else {
        const created = await api.createReview(form)
        navigate(`/reviews/${created.id}`, { replace: true })
        return
      }
      setSaved(true)
      setTimeout(() => setSaved(false), 3000)
    } catch (e) {
      setError(e.message)
    } finally {
      setSaving(false)
    }
  }

  async function handleDelete() {
    if (!confirm('Supprimer définitivement cet avis ?')) return
    setDel(true)
    try {
      await api.deleteReview(id)
      navigate('/reviews')
    } catch (e) {
      setError(e.message)
      setDel(false)
    }
  }

  if (loading) {
    return (
      <div>
        <PageHeader title={isEdit ? 'Modifier l\'avis' : 'Ajouter un avis'} />
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      </div>
    )
  }

  return (
    <div>
      <PageHeader
        title={isEdit ? 'Modifier l\'avis' : 'Nouvel avis'}
        actions={
          <div className="flex items-center gap-2">
            <Btn variant="ghost" size="sm" onClick={() => navigate('/reviews')}>
              <ArrowLeft size={13} />
              Retour
            </Btn>
            {isEdit && (
              <Btn variant="danger" size="sm" loading={deleting} onClick={handleDelete}>
                <Trash2 size={13} />
                Supprimer
              </Btn>
            )}
            <Btn size="sm" loading={saving} onClick={handleSubmit}>
              <Check size={13} />
              {isEdit ? 'Enregistrer' : 'Créer l\'avis'}
            </Btn>
          </div>
        }
      />

      <form onSubmit={handleSubmit} className="px-8 py-6 max-w-2xl">
        {error  && <div className="mb-4"><Notice type="error">{error}</Notice></div>}
        {saved  && <div className="mb-4"><Notice type="success">Enregistré avec succès.</Notice></div>}

        <div className="grid grid-cols-2 gap-5">
          {/* Auteur */}
          <div className="col-span-2">
            <Input
              label="Auteur *"
              value={form.author}
              onChange={e => set('author')(e.target.value)}
              placeholder="Prénom Nom"
              required
            />
          </div>

          {/* Note */}
          <div>
            <label className="flex flex-col gap-2">
              <span className="text-xs text-gray-500">Note *</span>
              <div className="flex items-center gap-3">
                <StarPicker value={form.rating} onChange={set('rating')} />
                <span className="text-sm text-gray-500">{form.rating}/5</span>
              </div>
            </label>
          </div>

          {/* Source */}
          <div>
            <Select label="Source" value={form.source} onChange={e => set('source')(e.target.value)}>
              {SOURCES.map(s => <option key={s.value} value={s.value}>{s.label}</option>)}
            </Select>
          </div>

          {/* Texte */}
          <div className="col-span-2">
            <Textarea
              label="Texte de l'avis"
              value={form.text}
              onChange={e => set('text')(e.target.value)}
              placeholder="Contenu de l'avis…"
              rows={5}
            />
          </div>

          {/* Lieu */}
          {lieux.length > 0 && (
            <div>
              <Select
                label="Lieu (optionnel)"
                value={form.lieu_id}
                onChange={e => set('lieu_id')(e.target.value)}
              >
                <option value="">— Aucun lieu —</option>
                {lieux.filter(l => l.active).map(l => (
                  <option key={l.id} value={l.id}>{l.name}</option>
                ))}
              </Select>
            </div>
          )}

          {/* Google Place ID */}
          <div className={lieux.length > 0 ? '' : 'col-span-2'}>
            <Input
              label="Google Place ID (optionnel)"
              value={form.place_id}
              onChange={e => set('place_id')(e.target.value)}
              placeholder="ChIJ…"
            />
          </div>

          {/* Certifié */}
          <div className="col-span-2 py-2">
            <Toggle
              label="Avis certifié / vérifié"
              checked={form.certified}
              onChange={set('certified')}
            />
          </div>
        </div>

        {/* Aperçu */}
        <div className="mt-8 border border-gray-200 p-5">
          <div className="text-xs text-gray-400 uppercase tracking-widest mb-4">Aperçu</div>
          <div className="flex flex-col gap-2">
            <Stars rating={form.rating} />
            {form.text && <p className="text-sm text-gray-700 italic">"{form.text}"</p>}
            <div className="flex items-center gap-2 mt-1">
              <div className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs">
                {form.author?.[0]?.toUpperCase() || '?'}
              </div>
              <span className="text-sm font-medium">{form.author || 'Auteur'}</span>
              {form.certified && (
                <span className="inline-block px-2 py-0.5 text-xs bg-black text-white">Certifié</span>
              )}
            </div>
          </div>
        </div>
      </form>
    </div>
  )
}
