import { useState, useEffect, useCallback, useRef } from 'react'
import { api } from '../lib/api'
import {
  PageHeader, Btn, Input, Select, Toggle,
  Notice, Spinner, Badge,
} from '../components/ui'
import { IconPencil, IconTrash, IconChevronDown, IconChevronUp, IconMapPin, IconPlus, IconRefresh, IconStar } from '../components/Icons'

const SOURCE_OPTIONS = [
  { value: 'google',      label: 'Google' },
  { value: 'tripadvisor', label: 'TripAdvisor' },
  { value: 'facebook',    label: 'Facebook' },
  { value: 'trustpilot',  label: 'Trustpilot' },
  { value: 'direct',      label: 'Direct' },
  { value: 'autre',       label: 'Autre' },
]

const EMPTY_FORM = { name: '', place_id: '', source: 'google', address: '', active: true }

function LieuForm({ initial = EMPTY_FORM, onSave, onCancel, saving }) {
  const [form, setForm] = useState(initial)
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  return (
    <form
      onSubmit={e => { e.preventDefault(); onSave(form) }}
      className="grid grid-cols-1 gap-4 sm:grid-cols-2"
    >
      <Input
        label="Nom du lieu *"
        value={form.name}
        onChange={e => set('name', e.target.value)}
        required
        placeholder="Boutique Paris — Marais"
      />
      <Select
        label="Plateforme"
        value={form.source}
        onChange={e => set('source', e.target.value)}
        options={SOURCE_OPTIONS}
      />
      <Input
        label="Place ID (Google Maps)"
        value={form.place_id}
        onChange={e => set('place_id', e.target.value)}
        placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4"
        className="font-mono text-xs"
      />
      <Input
        label="Adresse (optionnel)"
        value={form.address}
        onChange={e => set('address', e.target.value)}
        placeholder="1 rue de Rivoli, 75001 Paris"
      />
      <div className="flex items-center gap-3 sm:col-span-2">
        <Toggle
          checked={form.active}
          onChange={v => set('active', v)}
          id="lieu-active"
        />
        <label htmlFor="lieu-active" className="text-sm text-gray-700 cursor-pointer">
          Lieu actif (inclus dans les widgets)
        </label>
      </div>
      <div className="flex gap-2 sm:col-span-2">
        <Btn type="submit" disabled={saving}>{saving ? 'Enregistrement…' : 'Enregistrer'}</Btn>
        <Btn variant="ghost" type="button" onClick={onCancel}>Annuler</Btn>
      </div>
    </form>
  )
}

export default function Lieux() {
  const [lieux, setLieux]     = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError]     = useState(null)
  const [creating, setCreating] = useState(false)
  const [editingId, setEditingId] = useState(null)
  const [saving, setSaving]   = useState(false)
  const [expanded, setExpanded] = useState(null)
  const [notice, setNotice]   = useState(null)
  const [syncing, setSyncing] = useState(null)

  const load = useCallback(async () => {
    try {
      setLoading(true)
      setLieux(await api.lieux())
    } catch (e) {
      setError(e.message)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { load() }, [load])

  const flash = (msg, type = 'success') => {
    setNotice({ msg, type })
    setTimeout(() => setNotice(null), 3000)
  }

  const handleCreate = async (form) => {
    setSaving(true)
    try {
      const lieu = await api.createLieu(form)
      setLieux(l => [...l, lieu])
      setCreating(false)
      flash('Lieu créé.')
    } catch (e) {
      flash(e.message, 'error')
    } finally {
      setSaving(false)
    }
  }

  const handleUpdate = async (id, form) => {
    setSaving(true)
    try {
      const lieu = await api.updateLieu(id, form)
      setLieux(l => l.map(x => x.id === id ? lieu : x))
      setEditingId(null)
      flash('Lieu mis à jour.')
    } catch (e) {
      flash(e.message, 'error')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id, name) => {
    if (!window.confirm(`Supprimer « ${name} » ? Les avis liés ne seront pas supprimés.`)) return
    try {
      await api.deleteLieu(id)
      setLieux(l => l.filter(x => x.id !== id))
      flash('Lieu supprimé.')
    } catch (e) {
      flash(e.message, 'error')
    }
  }

  const pollRef = useRef(null)

  const stopPoll = () => {
    if (pollRef.current) { clearInterval(pollRef.current); pollRef.current = null }
  }

  const handleSyncGoogle = async (lieu) => {
    if (syncing) return
    setSyncing(lieu.id)
    try {
      await api.syncGoogle(lieu.id)
      // Polling toutes les 2s jusqu'à ce que le job soit terminé
      pollRef.current = setInterval(async () => {
        try {
          const s = await api.syncGoogleStatus(lieu.id)
          if (s.status === 'done') {
            stopPoll()
            setSyncing(null)
            flash(`✓ ${s.imported ?? 0} avis importés, ${s.skipped ?? 0} déjà existants.`)
            load()
          } else if (s.error) {
            stopPoll()
            setSyncing(null)
            flash(s.error, 'error')
          }
        } catch { stopPoll(); setSyncing(null) }
      }, 2000)
    } catch (e) {
      setSyncing(null)
      flash(e.message, 'error')
    }
  }

  // Nettoyer le poll si le composant est démonté
  useEffect(() => () => stopPoll(), [])

  if (loading) return <div className="flex justify-center py-20"><Spinner /></div>
  if (error)   return <Notice type="error">{error}</Notice>

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <PageHeader
        title="Lieux & Sources"
        sub={`${lieux.length} lieu${lieux.length !== 1 ? 'x' : ''}`}
        actions={
          !creating && (
            <Btn onClick={() => { setCreating(true); setEditingId(null) }}>
              <IconPlus className="w-4 h-4 mr-1" />
              Ajouter un lieu
            </Btn>
          )
        }
      />

      {notice && <Notice type={notice.type}>{notice.msg}</Notice>}

      {/* Formulaire de création */}
      {creating && (
        <div className="border border-gray-200 rounded-lg p-5 bg-gray-50">
          <h3 className="text-sm font-semibold text-gray-700 mb-4">Nouveau lieu</h3>
          <LieuForm
            onSave={handleCreate}
            onCancel={() => setCreating(false)}
            saving={saving}
          />
        </div>
      )}

      {/* Liste */}
      {lieux.length === 0 && !creating ? (
        <div className="text-center py-16 text-gray-400">
          <IconMapPin className="w-10 h-10 mx-auto mb-3 opacity-30" />
          <p className="text-sm">Aucun lieu configuré.</p>
          <p className="text-xs mt-1">Ajoutez vos boutiques ou points de vente pour filtrer les avis par lieu.</p>
        </div>
      ) : (
        <ul className="space-y-3">
          {lieux.map(lieu => (
            <li key={lieu.id} className="border border-gray-200 rounded-lg bg-white overflow-hidden">
              {/* Header de la ligne */}
              <div className="flex items-center gap-3 p-4">
                <IconMapPin className="w-4 h-4 text-gray-400 shrink-0" />
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 flex-wrap">
                    <span className="font-medium text-sm text-gray-900 truncate">{lieu.name}</span>
                    <Badge variant={lieu.source}>{SOURCE_OPTIONS.find(s => s.value === lieu.source)?.label ?? lieu.source}</Badge>
                    {!lieu.active && <Badge variant="warn">Inactif</Badge>}
                    {lieu.avis_count > 0 && (
                      <span className="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2 py-0.5">
                        <IconStar size={10} strokeWidth={1.5} />
                        {lieu.avis_count} avis
                      </span>
                    )}
                  </div>
                  {lieu.address && <p className="text-xs text-gray-400 truncate mt-0.5">{lieu.address}</p>}
                  {lieu.place_id && (
                    <p className="text-xs text-gray-400 font-mono truncate mt-0.5">{lieu.place_id}</p>
                  )}
                </div>
                <div className="flex items-center gap-1 shrink-0">
                  {lieu.source === 'google' && lieu.place_id && (
                    <Btn
                      variant="ghost"
                      size="sm"
                      onClick={() => handleSyncGoogle(lieu)}
                      loading={syncing === lieu.id}
                      title="Synchroniser depuis Google Places"
                    >
                      <IconRefresh size={13} strokeWidth={1.5} className={syncing === lieu.id ? 'animate-spin' : ''} />
                    </Btn>
                  )}
                  <Btn
                    variant="ghost"
                    size="sm"
                    onClick={() => setEditingId(editingId === lieu.id ? null : lieu.id)}
                    title="Modifier"
                  >
                    <IconPencil className="w-4 h-4" />
                  </Btn>
                  <Btn
                    variant="ghost"
                    size="sm"
                    onClick={() => handleDelete(lieu.id, lieu.name)}
                    title="Supprimer"
                    className="text-red-500 hover:text-red-700"
                  >
                    <IconTrash className="w-4 h-4" />
                  </Btn>
                  <Btn
                    variant="ghost"
                    size="sm"
                    onClick={() => setExpanded(expanded === lieu.id ? null : lieu.id)}
                    title="Voir l'ID"
                  >
                    {expanded === lieu.id
                      ? <IconChevronUp className="w-4 h-4" />
                      : <IconChevronDown className="w-4 h-4" />}
                  </Btn>
                </div>
              </div>

              {/* Formulaire d'édition inline */}
              {editingId === lieu.id && (
                <div className="border-t border-gray-100 p-4 bg-gray-50">
                  <LieuForm
                    initial={lieu}
                    onSave={form => handleUpdate(lieu.id, form)}
                    onCancel={() => setEditingId(null)}
                    saving={saving}
                  />
                </div>
              )}

              {/* Détail expandé : ID pour shortcode */}
              {expanded === lieu.id && editingId !== lieu.id && (
                <div className="border-t border-gray-100 p-4 bg-gray-50 space-y-2">
                  <p className="text-xs text-gray-500 font-semibold uppercase tracking-wide">Utilisation dans les shortcodes & widgets</p>
                  <code className="block text-xs bg-white border border-gray-200 rounded p-2 text-gray-700 font-mono">
                    {`[sj_reviews lieu_id="${lieu.id}"]`}
                  </code>
                  {lieu.place_id && (
                    <code className="block text-xs bg-white border border-gray-200 rounded p-2 text-gray-700 font-mono">
                      {`[sj_reviews place_id="${lieu.place_id}"]`}
                    </code>
                  )}
                </div>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
