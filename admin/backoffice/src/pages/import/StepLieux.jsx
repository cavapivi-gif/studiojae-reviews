import { useState, useEffect } from 'react'
import { api } from '../../lib/api'
import { Btn, Input, Select, Spinner, Toggle } from '../../components/ui'
import { IconCheck, IconPlus, IconTrash, IconMapPin, IconRefresh, IconChevronRight } from '../../components/Icons'
import { useToast } from '../../components/Toast'
import { EMPTY_LIEU, SOURCE_OPTIONS } from './constants'

/* ═══════════════════════════════════════════════════════════════════
   STEP 2 : Lieux — inline creation & management
   ═══════════════════════════════════════════════════════════════════ */
export default function StepLieux({ onNext, onBack }) {
  const toast = useToast()
  const [lieux, setLieux] = useState([])
  const [loading, setLoading] = useState(true)
  const [creating, setCreating] = useState(false)
  const [saving, setSaving] = useState(false)
  const [syncing, setSyncing] = useState(null)
  const [form, setForm] = useState({ ...EMPTY_LIEU })

  useEffect(() => {
    api.lieux()
      .then(setLieux)
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [])

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  const canSync = (lieu) => {
    if (lieu.source === 'google' && lieu.place_id) return true
    if (lieu.source === 'trustpilot' && lieu.trustpilot_domain) return true
    if (lieu.source === 'tripadvisor' && lieu.tripadvisor_location_id) return true
    return false
  }

  const handleSync = async (lieu) => {
    if (syncing) return
    setSyncing(lieu.id)
    try {
      let res
      if (lieu.source === 'trustpilot' && lieu.trustpilot_domain) {
        res = await api.syncTrustpilot(lieu.id)
      } else if (lieu.source === 'tripadvisor' && lieu.tripadvisor_location_id) {
        res = await api.syncTripadvisor(lieu.id)
      } else if (lieu.source === 'google' && lieu.place_id) {
        res = await api.syncGoogle(lieu.id)
      } else {
        toast.warn('Identifiants manquants pour la sync.')
        setSyncing(null)
        return
      }
      setLieux(prev => prev.map(l =>
        l.id === lieu.id
          ? { ...l, rating: res.rating, reviews_count: res.reviews_count, last_sync: res.last_sync }
          : l
      ))
      toast.success(`${lieu.name} : ${Number(res.rating).toFixed(1)}/5 · ${res.reviews_count?.toLocaleString()} avis`)
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSyncing(null)
    }
  }

  const handleSyncAll = async () => {
    const syncable = lieux.filter(canSync)
    if (!syncable.length) { toast.warn('Aucun lieu synchronisable.'); return }
    for (const lieu of syncable) {
      await handleSync(lieu)
    }
  }

  const handleCreate = async (e) => {
    e.preventDefault()
    if (!form.name.trim()) { toast.error('Le nom est requis.'); return }
    setSaving(true)
    try {
      const lieu = await api.createLieu(form)
      setLieux(l => [...l, lieu])
      setForm({ ...EMPTY_LIEU })
      setCreating(false)
      toast.success('Lieu créé.')
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id) => {
    try {
      await api.deleteLieu(id)
      setLieux(l => l.filter(x => x.id !== id))
      toast.success('Lieu supprimé.')
    } catch (e) {
      toast.error(e.message)
    }
  }

  if (loading) return <div className="flex items-center justify-center py-12"><Spinner size={20} /></div>

  const syncableCount = lieux.filter(canSync).length

  return (
    <div className="max-w-xl">
      <div className="mb-6">
        <h2 className="text-base font-semibold mb-1">Configurer vos lieux</h2>
        <p className="text-sm text-gray-500">
          Créez vos établissements et synchronisez les avis depuis vos plateformes.
        </p>
      </div>

      {/* Existing lieux */}
      {lieux.length > 0 && (
        <div className="border border-gray-200 rounded-lg divide-y divide-gray-100 mb-4">
          {lieux.map(l => (
            <div key={l.id} className="flex items-center justify-between px-4 py-3">
              <div className="flex items-center gap-3 min-w-0">
                <div className={`w-2 h-2 rounded-full flex-shrink-0 ${l.active ? 'bg-emerald-500' : 'bg-gray-300'}`} />
                <div className="min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="text-sm font-medium text-gray-900 truncate">{l.name}</p>
                    <span className="text-[10px] font-medium text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{l.source}</span>
                  </div>
                  <p className="text-xs text-gray-400">
                    {l.rating ? `${Number(l.rating).toFixed(1)}/5 · ${l.reviews_count ?? 0} avis` : l.address || 'Pas encore synchronisé'}
                    {l.last_sync && <span className="ml-1 text-gray-300">· sync {l.last_sync}</span>}
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-1 shrink-0">
                {canSync(l) && (
                  <button
                    type="button"
                    onClick={() => handleSync(l)}
                    disabled={!!syncing}
                    className="text-gray-400 hover:text-black transition-colors p-1.5 rounded hover:bg-gray-100"
                    title="Synchroniser les avis"
                  >
                    <IconRefresh size={14} strokeWidth={1.5} className={syncing === l.id ? 'animate-spin' : ''} />
                  </button>
                )}
                <button type="button" onClick={() => handleDelete(l.id)} className="text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded hover:bg-gray-100">
                  <IconTrash size={14} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Sync all button */}
      {syncableCount > 0 && (
        <div className="flex items-center gap-3 mb-4 p-3 border border-gray-200 rounded-lg bg-gray-50/50">
          <IconRefresh size={16} className="text-gray-400 shrink-0" />
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-gray-700">Synchroniser les avis</p>
            <p className="text-xs text-gray-400">{syncableCount} lieu{syncableCount > 1 ? 'x' : ''} synchronisable{syncableCount > 1 ? 's' : ''} (Google, Trustpilot, TripAdvisor)</p>
          </div>
          <Btn size="sm" variant="secondary" onClick={handleSyncAll} loading={!!syncing}>
            Sync tout
          </Btn>
        </div>
      )}

      {lieux.length === 0 && !creating && (
        <div className="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center mb-4">
          <IconMapPin size={24} className="mx-auto text-gray-300 mb-2" />
          <p className="text-sm text-gray-500 mb-3">Aucun lieu configuré</p>
          <Btn size="sm" onClick={() => setCreating(true)}>
            <IconPlus size={14} /> Créer un lieu
          </Btn>
        </div>
      )}

      {/* Inline create form */}
      {creating ? (
        <form onSubmit={handleCreate} className="border border-gray-200 rounded-lg p-4 mb-4 space-y-3 bg-gray-50/50">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-widest">Nouveau lieu</p>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input label="Nom *" value={form.name} onChange={e => set('name', e.target.value)} placeholder="Restaurant Le Marais" required />
            <Select label="Plateforme" value={form.source} onChange={e => set('source', e.target.value)}>
              {SOURCE_OPTIONS.map(s => <option key={s.value} value={s.value}>{s.label}</option>)}
            </Select>
            <Input label="Place ID (Google)" value={form.place_id} onChange={e => set('place_id', e.target.value)} placeholder="ChIJN1t..." className="font-mono text-xs" />
            <Input label="Adresse" value={form.address} onChange={e => set('address', e.target.value)} placeholder="1 rue de Rivoli, 75001 Paris" />
            {form.source === 'trustpilot' && (
              <Input label="Domaine Trustpilot" value={form.trustpilot_domain} onChange={e => set('trustpilot_domain', e.target.value)} placeholder="monsite.com" className="font-mono text-xs" />
            )}
            {form.source === 'tripadvisor' && (
              <Input label="Location ID TripAdvisor" value={form.tripadvisor_location_id} onChange={e => set('tripadvisor_location_id', e.target.value)} placeholder="188757" className="font-mono text-xs" />
            )}
          </div>
          <div className="flex items-center gap-3">
            <Toggle checked={form.active} onChange={v => set('active', v)} id="onb-lieu-active" />
            <label htmlFor="onb-lieu-active" className="text-sm text-gray-600 cursor-pointer">Lieu actif</label>
          </div>
          <div className="flex gap-2">
            <Btn type="submit" size="sm" loading={saving}>Créer</Btn>
            <Btn type="button" variant="ghost" size="sm" onClick={() => setCreating(false)}>Annuler</Btn>
          </div>
        </form>
      ) : lieux.length > 0 && (
        <button
          type="button"
          onClick={() => setCreating(true)}
          className="flex items-center gap-1.5 text-sm text-gray-500 hover:text-black transition-colors mb-4"
        >
          <IconPlus size={14} /> Ajouter un autre lieu
        </button>
      )}

      <div className="flex justify-between items-center mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={onNext}>
          {lieux.length > 0 ? 'Continuer' : 'Continuer sans lieu'}
          <IconChevronRight size={14} />
        </Btn>
      </div>
    </div>
  )
}
