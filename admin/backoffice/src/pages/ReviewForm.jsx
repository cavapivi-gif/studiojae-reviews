import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, Btn, Input, Textarea, Select, Toggle, Spinner, Stars, StarPicker } from '../components/ui'
import { IconCheck, IconArrowLeft, IconTrash } from '../components/Icons'
import { useToast } from '../components/Toast'

const SOURCES = [
  { value: 'google',      label: 'Google' },
  { value: 'tripadvisor', label: 'TripAdvisor' },
  { value: 'facebook',    label: 'Facebook' },
  { value: 'trustpilot',  label: 'Trustpilot' },
  { value: 'regiondo',    label: 'Regiondo' },
  { value: 'direct',      label: 'Direct' },
  { value: 'autre',       label: 'Autre' },
]

const CRITERIA = [
  { key: 'qualite_prix', label: 'Qualité/prix' },
  { key: 'ambiance',     label: 'Ambiance'     },
  { key: 'experience',   label: 'Expérience'   },
  { key: 'paysage',      label: 'Paysage'      },
]

const LANGUAGES = [
  { value: 'fr', label: 'Français'  },
  { value: 'en', label: 'Anglais'   },
  { value: 'it', label: 'Italien'   },
  { value: 'de', label: 'Allemand'  },
  { value: 'es', label: 'Espagnol'  },
]

const TRAVEL_TYPES = [
  { value: '',          label: '— Non précisé —'     },
  { value: 'couple',    label: 'Couple'               },
  { value: 'solo',      label: 'Solo'                 },
  { value: 'famille',   label: 'Famille'              },
  { value: 'amis',      label: 'Entre amis'           },
  { value: 'affaires',  label: "Voyage d'affaires"    },
]

const EMPTY = {
  author: '', avis_title: '', rating: 5, text: '', certified: false,
  source: 'google', lieu_id: '', linked_post_id: 0,
  qualite_prix: 0, ambiance: 0, experience: 0, paysage: 0,
  visit_date: '', language: 'fr', travel_type: '',
}

function CritPicker({ label, value, onChange }) {
  return (
    <div className="flex flex-col gap-1.5">
      <span className="text-xs font-medium text-gray-500 uppercase tracking-wide">{label}</span>
      <div className="flex items-center gap-1">
        <button
          type="button" title="Non noté" onClick={() => onChange(0)}
          className={`h-8 px-2.5 text-xs font-semibold transition-all duration-150 border
            ${value === 0 ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-400 border-gray-200 hover:border-gray-400 hover:text-gray-600'}`}
        >—</button>
        {[1, 2, 3, 4, 5].map(n => (
          <button
            key={n} type="button" onClick={() => onChange(n)}
            className={`w-8 h-8 text-sm font-semibold transition-all duration-150 border
              ${value >= n ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-400 border-gray-200 hover:border-gray-400 hover:text-gray-600'}`}
          >{n}</button>
        ))}
        {value > 0 && <span className="ml-1 text-xs text-gray-400">{value}/5</span>}
      </div>
    </div>
  )
}

function SectionLabel({ children }) {
  return (
    <div className="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 pb-2 border-b border-gray-100">
      {children}
    </div>
  )
}

export default function ReviewForm() {
  const { id }    = useParams()
  const navigate  = useNavigate()
  const toast     = useToast()
  const isEdit    = Boolean(id)

  const [form, setForm]             = useState(EMPTY)
  const [loading, setLoading]       = useState(isEdit)
  const [saving, setSaving]         = useState(false)
  const [deleting, setDel]          = useState(false)
  const [lieux, setLieux]           = useState([])
  const [linkedPosts, setLinkedPosts]       = useState([])
  const [hasLinkedTypes, setHasLinkedTypes] = useState(false)
  const [criteriaLabels, setCriteriaLabels] = useState({})

  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
    api.settings()
      .then(s => {
        const types = Array.isArray(s.linked_post_types) ? s.linked_post_types : []
        if (types.length > 0) {
          setHasLinkedTypes(true)
          api.linkedPosts().then(setLinkedPosts).catch(() => {})
        }
        if (s.criteria_labels) setCriteriaLabels(s.criteria_labels)
      })
      .catch(() => {})
  }, [])

  useEffect(() => {
    if (!isEdit) return
    api.review(id)
      .then(r => setForm({
        author:         r.author        ?? '',
        avis_title:     r.avis_title    ?? '',
        rating:         r.rating        ?? 5,
        text:           r.text          ?? '',
        certified:      r.certified     ?? false,
        source:         r.source        ?? 'google',
        lieu_id:        r.lieu_id       ?? '',
        linked_post_id: r.linked_post_id ?? 0,
        qualite_prix:   r.qualite_prix  ?? 0,
        ambiance:       r.ambiance      ?? 0,
        experience:     r.experience    ?? 0,
        paysage:        r.paysage       ?? 0,
        visit_date:     r.visit_date    ?? '',
        language:       r.language      ?? 'fr',
        travel_type:    r.travel_type   ?? '',
      }))
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [id, isEdit])

  function set(key) {
    return value => setForm(f => ({ ...f, [key]: value }))
  }

  async function handleSubmit(e) {
    e?.preventDefault()
    setSaving(true)
    try {
      if (isEdit) {
        await api.updateReview(id, form)
        toast.success('Avis enregistré.')
      } else {
        const created = await api.createReview(form)
        toast.success('Avis créé avec succès.')
        navigate(`/reviews/${created.id}`, { replace: true })
        return
      }
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  async function handleDelete() {
    if (!confirm('Supprimer définitivement cet avis ?')) return
    setDel(true)
    try {
      await api.deleteReview(id)
      toast.success('Avis supprimé.')
      navigate('/reviews')
    } catch (e) {
      toast.error(e.message)
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

  const SaveBtn = ({ className = '' }) => (
    <Btn size="sm" loading={saving} onClick={handleSubmit} className={className}>
      <IconCheck size={13} />
      {isEdit ? 'Enregistrer' : 'Créer l\'avis'}
    </Btn>
  )

  return (
    <div>
      <PageHeader
        title={isEdit ? 'Modifier l\'avis' : 'Nouvel avis'}
        actions={
          <div className="flex items-center gap-2">
            <Btn variant="ghost" size="sm" onClick={() => navigate('/reviews')}>
              <IconArrowLeft size={13} />
              Retour
            </Btn>
            {isEdit && (
              <Btn variant="danger" size="sm" loading={deleting} onClick={handleDelete}>
                <IconTrash size={13} />
                Supprimer
              </Btn>
            )}
            <SaveBtn />
          </div>
        }
      />

      <form onSubmit={handleSubmit} className="px-8 py-6 max-w-2xl">

        {/* ── Infos principales ─────────────────────────────────── */}
        <div className="mb-6">
          <SectionLabel>Informations</SectionLabel>
          <div className="grid grid-cols-2 gap-5">
            <div>
              <Input label="Auteur *" value={form.author} onChange={e => set('author')(e.target.value)} placeholder="Prénom Nom" required />
            </div>
            <div>
              <Input label="Titre de l'avis" value={form.avis_title} onChange={e => set('avis_title')(e.target.value)} placeholder="Ex : Excellent service, très pro…" />
            </div>
            <div>
              <label className="flex flex-col gap-2">
                <span className="text-xs text-gray-500">Note *</span>
                <div className="flex items-center gap-3">
                  <StarPicker value={form.rating} onChange={set('rating')} />
                  <span className="text-sm text-gray-500">{form.rating}/5</span>
                </div>
              </label>
            </div>
            <div>
              <Select label="Source" value={form.source} onChange={e => set('source')(e.target.value)}>
                {SOURCES.map(s => <option key={s.value} value={s.value}>{s.label}</option>)}
              </Select>
            </div>
            <div className="col-span-2">
              <Textarea label="Texte de l'avis" value={form.text} onChange={e => set('text')(e.target.value)} placeholder="Contenu de l'avis…" rows={5} />
            </div>
          </div>
        </div>

        {/* ── Lieu & liaison ────────────────────────────────────── */}
        <div className="mb-6">
          <SectionLabel>Lieu & liaison</SectionLabel>
          <div className="grid grid-cols-2 gap-5">
            <div className="col-span-2">
              <Select label="Lieu rattaché" value={form.lieu_id} onChange={e => set('lieu_id')(e.target.value)}>
                <option value="">— Aucun lieu —</option>
                {lieux.filter(l => l.active).map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
              </Select>
              {lieux.length === 0 && <p className="mt-1 text-xs text-gray-400">Aucun lieu configuré. Ajoutez-en dans <strong>Lieux & Sources</strong>.</p>}
            </div>
            {hasLinkedTypes && (
              <div className="col-span-2">
                <Select label="Post lié (optionnel)" value={form.linked_post_id || ''} onChange={e => set('linked_post_id')(parseInt(e.target.value, 10) || 0)}>
                  <option value="">— Aucun —</option>
                  {linkedPosts.map(p => <option key={p.id} value={p.id}>{p.title} ({p.post_type})</option>)}
                </Select>
              </div>
            )}
            <div><Input label="Date de visite" type="date" value={form.visit_date} onChange={e => set('visit_date')(e.target.value)} /></div>
            <div>
              <Select label="Type de voyage" value={form.travel_type} onChange={e => set('travel_type')(e.target.value)}>
                {TRAVEL_TYPES.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
              </Select>
            </div>
            <div>
              <Select label="Langue de l'avis" value={form.language} onChange={e => set('language')(e.target.value)}>
                {LANGUAGES.map(l => <option key={l.value} value={l.value}>{l.label}</option>)}
              </Select>
            </div>
            <div className="py-1 flex items-end">
              <Toggle label="Avis certifié / vérifié" checked={form.certified} onChange={set('certified')} />
            </div>
          </div>
        </div>

        {/* ── Sous-critères ─────────────────────────────────────── */}
        <div className="mb-6">
          <SectionLabel>
            Sous-critères
            <span className="ml-2 font-normal text-gray-400 normal-case tracking-normal">(optionnel — 0 = non noté)</span>
          </SectionLabel>
          <div className="grid grid-cols-2 gap-5">
            {CRITERIA.map(c => (
              <CritPicker
                key={c.key}
                label={criteriaLabels[c.key] || c.label}
                value={form[c.key]}
                onChange={set(c.key)}
              />
            ))}
          </div>
        </div>

        {/* ── Aperçu ────────────────────────────────────────────── */}
        <div className="border border-gray-200 p-5 bg-gray-50/50">
          <div className="text-xs text-gray-400 uppercase tracking-widest mb-4">Aperçu</div>
          <div className="flex flex-col gap-2">
            <Stars rating={form.rating} />
            {form.text && <p className="text-sm text-gray-700 italic">"{form.text}"</p>}
            <div className="flex items-center gap-2 mt-1">
              <div className="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold">
                {form.author?.[0]?.toUpperCase() || '?'}
              </div>
              <span className="text-sm font-medium">{form.author || 'Auteur'}</span>
              {form.certified && <span className="inline-block px-2 py-0.5 text-xs bg-black text-white">Certifié</span>}
            </div>
            {(form.visit_date || form.travel_type) && (
              <p className="text-xs text-gray-400 mt-1">
                {form.visit_date && new Date(form.visit_date).toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' })}
                {form.visit_date && form.travel_type && ' · '}
                {form.travel_type && TRAVEL_TYPES.find(t => t.value === form.travel_type)?.label}
              </p>
            )}
            {CRITERIA.some(c => form[c.key] > 0) && (
              <div className="flex flex-wrap gap-3 mt-2 pt-2 border-t border-gray-200">
                {CRITERIA.filter(c => form[c.key] > 0).map(c => (
                  <span key={c.key} className="text-xs text-gray-500">
                    {criteriaLabels[c.key] || c.label} <strong className="text-gray-800">{form[c.key]}/5</strong>
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* ── Sticky save button bottom ─────────────────────────── */}
        <div className="sticky bottom-0 bg-white border-t border-gray-200 -mx-8 px-8 py-3 mt-8 flex items-center justify-between">
          <Btn variant="ghost" size="sm" onClick={() => navigate('/reviews')}>
            <IconArrowLeft size={13} />
            Retour
          </Btn>
          <div className="flex items-center gap-2">
            {isEdit && (
              <Btn variant="danger" size="sm" loading={deleting} onClick={handleDelete}>
                <IconTrash size={13} />
                Supprimer
              </Btn>
            )}
            <SaveBtn />
          </div>
        </div>
      </form>
    </div>
  )
}
