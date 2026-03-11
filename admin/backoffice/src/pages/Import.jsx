import { useState, useEffect, useCallback, useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import { PageHeader, Btn, Input, Select, Notice, Spinner, Toggle } from '../components/ui'
import { IconCheck, IconPlus, IconTrash, IconKey, IconMapPin, IconUpload, IconRocket, IconChevronRight, IconRefresh } from '../components/Icons'
import { useToast } from '../components/Toast'
import { SOURCE_LABELS } from '../lib/constants'

/* ── Constantes ──────────────────────────────────────────────────── */
const ONBOARDING_STEPS = [
  { id: 'sources', label: 'Sources API', icon: IconKey },
  { id: 'lieux',   label: 'Lieux',       icon: IconMapPin },
  { id: 'import',  label: 'Import CSV',  icon: IconUpload },
  { id: 'done',    label: 'Terminé',     icon: IconRocket },
]

const CSV_STEPS = ['Fichier', 'Colonnes', 'Défauts', 'Produits', 'Aperçu']

// Champs SJ disponibles pour le mapping
const SJ_FIELDS = [
  { value: '_ignore',      label: '— Ignorer —' },
  { value: 'product',      label: 'Produit (nom)' },
  { value: 'order_id',     label: 'N° de commande' },
  { value: 'booking_date', label: 'Date de réservation' },
  { value: 'visit_date',   label: 'Date de l\'évènement' },
  { value: 'eval_date',    label: 'Date d\'évaluation' },
  { value: 'author',       label: 'Nom du client' },
  { value: 'email',        label: 'Email client' },
  { value: 'phone',        label: 'Téléphone' },
  { value: 'rating',       label: 'Note (1–5)' },
  { value: 'title',        label: 'Résumé / Titre' },
  { value: 'text',         label: 'Texte de l\'avis' },
]

const AUTO_DETECT_RULES = [
  { patterns: ['email', 'mail', 'courriel'], field: 'email' },
  { patterns: ['phone', 'téléphone', 'telephone', 'mobile'], field: 'phone' },
  { patterns: ['commande', 'order', 'n°', 'numéro', 'numero', 'booking_id'], field: 'order_id' },
  { required: ['date'], patterns: ['réservation', 'reservation', 'booking'], field: 'booking_date' },
  { required: ['date'], patterns: ['évènement', 'evenement', 'visite', 'event'], field: 'visit_date' },
  { required: ['date'], patterns: ['évaluation', 'evaluation', 'avis', 'submitted', 'eval'], field: 'eval_date' },
  { patterns: ['nom', 'name', 'auteur', 'author', 'prénom'], field: 'author' },
  { patterns: ['résumé', 'resume', 'summary', 'titre', 'title'], field: 'title' },
  { patterns: ['note', 'rating', 'étoile', 'star', 'score'], field: 'rating' },
  { patterns: ['texte', 'text', 'commentaire', 'comment', 'avis', 'review', 'évaluation', 'evaluation'], field: 'text' },
  { patterns: ['produit', 'product', 'excursion', 'service'], field: 'product' },
]

const SOURCE_OPTIONS = [
  { value: 'google',      label: 'Google' },
  { value: 'tripadvisor', label: 'TripAdvisor' },
  { value: 'facebook',    label: 'Facebook' },
  { value: 'trustpilot',  label: 'Trustpilot' },
  { value: 'regiondo',    label: 'Regiondo' },
  { value: 'direct',      label: 'Direct' },
  { value: 'autre',       label: 'Autre' },
]

const EMPTY_LIEU = { name: '', place_id: '', source: 'google', address: '', active: true, trustpilot_domain: '', tripadvisor_location_id: '' }

function detectField(header, sampleValues = []) {
  const h = header.toLowerCase().trim()
  if (/^[éeè]valuation$/i.test(h.replace(/\s+/g, ''))) {
    const nums = sampleValues.map(v => parseFloat(String(v).replace(',', '.'))).filter(n => !isNaN(n))
    if (nums.length > 0 && nums.every(n => n >= 1 && n <= 5)) return 'rating'
    return 'text'
  }
  for (const rule of AUTO_DETECT_RULES) {
    if (rule.required && !rule.required.every(r => h.includes(r))) continue
    if (rule.patterns.some(p => h.includes(p))) return rule.field
  }
  return '_ignore'
}

/* ── Helpers CSV ─────────────────────────────────────────────────── */
function parseCSV(text) {
  const firstNewline = text.indexOf('\n')
  const firstLine = (firstNewline >= 0 ? text.slice(0, firstNewline) : text).replace(/\r$/, '').trim()
  const sep = firstLine.includes(';') ? ';' : firstLine.includes('\t') ? '\t' : ','

  const rows = []
  let row = []
  let current = ''
  let inQuotes = false

  for (let i = 0; i < text.length; i++) {
    const c = text[i]
    if (inQuotes) {
      if (c === '"') {
        if (text[i + 1] === '"') { current += '"'; i++ }
        else inQuotes = false
      } else if (c === '\r') {
        // ignore
      } else {
        current += c
      }
    } else {
      if (c === '"') {
        inQuotes = true
      } else if (c === sep) {
        row.push(current.trim())
        current = ''
      } else if (c === '\r') {
        // ignore
      } else if (c === '\n') {
        row.push(current.trim())
        current = ''
        if (row.some(cell => cell !== '')) rows.push(row)
        row = []
      } else {
        current += c
      }
    }
  }
  if (current || row.length > 0) {
    row.push(current.trim())
    if (row.some(cell => cell !== '')) rows.push(row)
  }
  if (rows.length < 2) return null
  return { headers: rows[0], rows: rows.slice(1), sep }
}

function mapRowToSJ(rawRow, headers, columnMap) {
  const mapped = {}
  headers.forEach((header, i) => {
    const field = columnMap[i]
    if (field && field !== '_ignore') {
      mapped[field] = (rawRow[i] ?? '').trim()
    }
  })
  if (mapped.rating) {
    const parsed = parseFloat(String(mapped.rating).replace(',', '.'))
    mapped.rating = !isNaN(parsed) ? Math.round(Math.min(5, Math.max(1, parsed))) : 0
  }
  return mapped
}

/* ── Onboarding step indicator ───────────────────────────────────── */
function OnboardingNav({ current, onGo, completedSteps }) {
  return (
    <div className="flex items-center gap-0 mb-8">
      {ONBOARDING_STEPS.map((step, i) => {
        const Icon = step.icon
        const done = completedSteps.includes(step.id)
        const active = i === current
        return (
          <div key={step.id} className="flex items-center">
            <button
              type="button"
              onClick={() => onGo(i)}
              className={`flex items-center gap-2 px-3 py-2 text-sm font-medium rounded transition-colors ${
                active
                  ? 'bg-black text-white'
                  : done
                  ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100'
                  : 'text-gray-400 hover:text-gray-600'
              }`}
            >
              <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                active ? 'bg-white text-black' : done ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-500'
              }`}>
                {done ? <IconCheck size={12} strokeWidth={3} /> : i + 1}
              </span>
              <span className="hidden sm:inline">{step.label}</span>
            </button>
            {i < ONBOARDING_STEPS.length - 1 && (
              <div className={`w-8 h-px ${i < current ? 'bg-emerald-300' : 'bg-gray-200'}`} />
            )}
          </div>
        )
      })}
    </div>
  )
}

/* ── CSV Step Bar ─────────────────────────────────────────────────── */
function CsvStepBar({ current }) {
  return (
    <div className="flex items-center gap-0 mb-6">
      {CSV_STEPS.map((label, i) => (
        <div key={label} className="flex items-center">
          <div className={`flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded ${
            i === current
              ? 'bg-black text-white'
              : i < current
              ? 'text-gray-500 bg-gray-100'
              : 'text-gray-400'
          }`}>
            <span className={`w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold ${
              i === current ? 'bg-white text-black' : i < current ? 'bg-gray-400 text-white' : 'bg-gray-200 text-gray-500'
            }`}>
              {i < current ? '✓' : i + 1}
            </span>
            {label}
          </div>
          {i < CSV_STEPS.length - 1 && (
            <div className={`w-6 h-px ${i < current ? 'bg-gray-400' : 'bg-gray-200'}`} />
          )}
        </div>
      ))}
    </div>
  )
}

/* ═══════════════════════════════════════════════════════════════════
   STEP 1 : Sources API — inline configuration
   ═══════════════════════════════════════════════════════════════════ */
function StepSources({ onNext, onSkip }) {
  const toast = useToast()
  const [settings, setSettings] = useState(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)

  const [googleKey, setGoogleKey] = useState('')
  const [trustpilotKey, setTrustpilotKey] = useState('')
  const [tripadvisorKey, setTripadvisorKey] = useState('')

  const [googleStatus, setGoogleStatus] = useState(null)
  const [trustpilotStatus, setTrustpilotStatus] = useState(null)
  const [tripadvisorStatus, setTripadvisorStatus] = useState(null)
  const [googleMsg, setGoogleMsg] = useState('')
  const [trustpilotMsg, setTrustpilotMsg] = useState('')
  const [tripadvisorMsg, setTripadvisorMsg] = useState('')

  useEffect(() => {
    api.settings().then(s => {
      setSettings(s)
      setGoogleKey(s.google_api_key || '')
      setTrustpilotKey(s.trustpilot_api_key || '')
      setTripadvisorKey(s.tripadvisor_api_key || '')
      if (s.google_api_key) setGoogleStatus('ok')
      if (s.trustpilot_api_key) setTrustpilotStatus('ok')
      if (s.tripadvisor_api_key) setTripadvisorStatus('ok')
    }).catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [])

  const testKey = async (type) => {
    const setStatus = { google: setGoogleStatus, trustpilot: setTrustpilotStatus, tripadvisor: setTripadvisorStatus }[type]
    const setMsg = { google: setGoogleMsg, trustpilot: setTrustpilotMsg, tripadvisor: setTripadvisorMsg }[type]
    const key = { google: googleKey, trustpilot: trustpilotKey, tripadvisor: tripadvisorKey }[type]
    const testFn = { google: api.testGoogleKey, trustpilot: api.testTrustpilotKey, tripadvisor: api.testTripadvisorKey }[type]

    if (!key.trim()) return
    setStatus('testing')
    try {
      const res = await testFn(key)
      setStatus(res.ok ? 'ok' : 'error')
      setMsg(res.message ?? '')
    } catch (e) {
      setStatus('error')
      setMsg(e.message)
    }
  }

  const handleSave = async () => {
    setSaving(true)
    try {
      await api.saveSettings({
        ...settings,
        google_api_key: googleKey,
        trustpilot_api_key: trustpilotKey,
        tripadvisor_api_key: tripadvisorKey,
      })
      toast.success('Clés API enregistrées.')
      onNext()
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  if (loading) return <div className="flex items-center justify-center py-12"><Spinner size={20} /></div>

  const anyKey = googleKey.trim() || trustpilotKey.trim() || tripadvisorKey.trim()

  return (
    <div className="max-w-xl">
      <div className="mb-6">
        <h2 className="text-base font-semibold mb-1">Configurer vos sources d'avis</h2>
        <p className="text-sm text-gray-500">
          Ajoutez les clés API des plateformes que vous utilisez. Vous pouvez en ajouter une seule ou toutes.
        </p>
      </div>

      <div className="space-y-5">
        {/* Google */}
        <ApiCard
          title="Google Places"
          badge="Recommandé"
          badgeColor="blue"
          description="Importez automatiquement les avis Google de vos établissements."
          value={googleKey}
          onChange={setGoogleKey}
          placeholder="AIzaSy..."
          status={googleStatus}
          statusMsg={googleMsg}
          onTest={() => testKey('google')}
        />

        {/* Trustpilot */}
        <ApiCard
          title="Trustpilot"
          description="Synchronisez les avis de votre page Trustpilot Business."
          value={trustpilotKey}
          onChange={setTrustpilotKey}
          placeholder="Clé API Trustpilot..."
          status={trustpilotStatus}
          statusMsg={trustpilotMsg}
          onTest={() => testKey('trustpilot')}
        />

        {/* TripAdvisor */}
        <ApiCard
          title="TripAdvisor"
          description="Récupérez les avis depuis TripAdvisor Content API."
          value={tripadvisorKey}
          onChange={setTripadvisorKey}
          placeholder="Clé API TripAdvisor..."
          status={tripadvisorStatus}
          statusMsg={tripadvisorMsg}
          onTest={() => testKey('tripadvisor')}
        />
      </div>

      <div className="flex justify-between items-center mt-8">
        <button type="button" onClick={onSkip} className="text-sm text-gray-400 hover:text-gray-600">
          Passer cette étape
        </button>
        <Btn onClick={handleSave} loading={saving}>
          {anyKey ? 'Enregistrer et continuer' : 'Continuer sans clé'}
          <IconChevronRight size={14} />
        </Btn>
      </div>
    </div>
  )
}

function ApiCard({ title, badge, badgeColor, description, value, onChange, placeholder, status, statusMsg, onTest }) {
  const [open, setOpen] = useState(!!value)
  const colors = {
    blue: 'bg-blue-50 text-blue-700 border-blue-200',
    green: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  }

  return (
    <div className={`border rounded-lg transition-colors ${value && status === 'ok' ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-200'}`}>
      <button
        type="button"
        onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between px-4 py-3 text-left"
      >
        <div className="flex items-center gap-2">
          <span className="text-sm font-semibold text-gray-900">{title}</span>
          {badge && <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded border ${colors[badgeColor] || colors.green}`}>{badge}</span>}
          {status === 'ok' && value && <IconCheck size={14} strokeWidth={2.5} className="text-emerald-600" />}
        </div>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" className={`text-gray-400 transition-transform ${open ? 'rotate-180' : ''}`}>
          <path d="M3 5l4 4 4-4" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      </button>
      {open && (
        <div className="px-4 pb-4 space-y-3 border-t border-gray-100 pt-3">
          <p className="text-xs text-gray-500">{description}</p>
          <div className="flex gap-2 items-end">
            <div className="flex-1">
              <input
                type="password"
                value={value}
                onChange={e => onChange(e.target.value)}
                placeholder={placeholder}
                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                autoComplete="off"
              />
            </div>
            <Btn type="button" variant="secondary" size="sm" onClick={onTest} disabled={!value.trim() || status === 'testing'} loading={status === 'testing'}>
              Tester
            </Btn>
          </div>
          {status === 'ok' && <p className="text-xs text-emerald-600 flex items-center gap-1"><IconCheck size={12} strokeWidth={2.5} /> Clé valide.{statusMsg ? ` ${statusMsg}` : ''}</p>}
          {status === 'error' && <p className="text-xs text-red-600">{statusMsg || 'Clé invalide.'}</p>}
        </div>
      )}
    </div>
  )
}

/* ═══════════════════════════════════════════════════════════════════
   STEP 2 : Lieux — inline creation & management
   ═══════════════════════════════════════════════════════════════════ */
function StepLieux({ onNext, onBack }) {
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

/* ═══════════════════════════════════════════════════════════════════
   STEP 3 : Import CSV — full wizard embedded inline
   ═══════════════════════════════════════════════════════════════════ */
function StepImport({ onNext, onBack }) {
  const navigate = useNavigate()
  const [csvStep, setCsvStep] = useState(0)
  const [csvState, setCsvState] = useState({})

  const csvNext = (data) => {
    setCsvState(prev => ({ ...prev, ...data }))
    setCsvStep(s => s + 1)
  }
  const csvBack = () => setCsvStep(s => Math.max(0, s - 1))

  return (
    <div>
      <div className="mb-6">
        <h2 className="text-base font-semibold mb-1">Importer des avis CSV</h2>
        <p className="text-sm text-gray-500">
          Si vous avez des avis dans un fichier CSV (export Regiondo, tableur...), importez-les ici.
          Les avis Google, Trustpilot et TripAdvisor se synchronisent automatiquement via l'étape précédente.
        </p>
      </div>

      {csvStep === 0 && (
        <CsvStep1File
          onNext={csvNext}
          onSkip={onNext}
          onBack={onBack}
        />
      )}
      {csvStep === 1 && csvState.headers && (
        <>
          <CsvStepBar current={1} />
          <CsvStep2Columns data={csvState} onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 2 && (
        <>
          <CsvStepBar current={2} />
          <CsvStep3Defaults onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 3 && (
        <>
          <CsvStepBar current={3} />
          <CsvStep4Products data={csvState} columnMap={csvState.columnMap ?? {}} onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 4 && (
        <>
          <CsvStepBar current={4} />
          <CsvStep5Preview
            data={csvState}
            columnMap={csvState.columnMap ?? {}}
            defaults={csvState.defaults ?? {}}
            productMap={csvState.productMap ?? {}}
            onBack={csvBack}
            onDone={onNext}
          />
        </>
      )}
    </div>
  )
}

/* ── CSV Step 1 : Fichier ────────────────────────────────────────── */
function CsvStep1File({ onNext, onSkip, onBack }) {
  const [csvText, setCsvText] = useState('')
  const [error, setError] = useState('')
  const [dragging, setDragging] = useState(false)

  const handleFile = (file) => {
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => setCsvText(e.target.result)
    reader.readAsText(file, 'utf-8')
  }

  const handleDrop = useCallback((e) => {
    e.preventDefault()
    setDragging(false)
    const file = e.dataTransfer.files[0]
    if (file) handleFile(file)
  }, [])

  const handleAnalyze = () => {
    if (!csvText.trim()) { setError('Collez du CSV ou chargez un fichier.'); return }
    const parsed = parseCSV(csvText)
    if (!parsed || parsed.headers.length < 2) { setError('Format CSV invalide ou trop peu de colonnes.'); return }
    if (parsed.rows.length === 0) { setError('Aucune ligne de données trouvée.'); return }
    setError('')
    onNext({ csvText, ...parsed })
  }

  return (
    <div className="max-w-2xl">
      <CsvStepBar current={0} />

      {/* Drop zone */}
      <div
        className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors mb-4 ${
          dragging ? 'border-black bg-gray-50' : 'border-gray-300 hover:border-gray-400'
        }`}
        onDragOver={(e) => { e.preventDefault(); setDragging(true) }}
        onDragLeave={() => setDragging(false)}
        onDrop={handleDrop}
        onClick={() => document.getElementById('csv-file-input').click()}
      >
        <IconUpload size={24} className="mx-auto text-gray-400 mb-2" />
        <p className="text-sm text-gray-600">Glissez un fichier CSV ici, ou <span className="underline">cliquez pour choisir</span></p>
        <p className="text-xs text-gray-400 mt-1">Format : UTF-8, séparateur virgule ou point-virgule</p>
        <input
          id="csv-file-input"
          type="file"
          accept=".csv,.txt"
          className="hidden"
          onChange={(e) => handleFile(e.target.files[0])}
        />
      </div>

      <div className="relative mb-4">
        <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-gray-200" /></div>
        <div className="relative flex justify-center text-xs text-gray-400"><span className="bg-white px-3">ou collez le contenu CSV</span></div>
      </div>

      <textarea
        className="w-full border border-gray-200 rounded p-3 text-xs font-mono h-40 resize-y focus:outline-none focus:border-gray-400"
        placeholder="Produit;N° commande;Date réservation;..."
        value={csvText}
        onChange={(e) => setCsvText(e.target.value)}
      />

      {error && <Notice type="error" className="mt-3">{error}</Notice>}
      {csvText && (
        <p className="text-xs text-gray-400 mt-2">
          {csvText.trim().split('\n').length - 1} ligne(s) détectée(s)
        </p>
      )}

      <div className="flex justify-between items-center mt-6">
        <div className="flex items-center gap-3">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <button type="button" onClick={onSkip} className="text-sm text-gray-400 hover:text-gray-600">
            Passer l'import
          </button>
        </div>
        <Btn onClick={handleAnalyze} disabled={!csvText.trim()}>Analyser</Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 2 : Colonnes ───────────────────────────────────────── */
function CsvStep2Columns({ data, onNext, onBack }) {
  const [columnMap, setColumnMap] = useState(() => {
    const map = {}
    data.headers.forEach((h, i) => {
      const samples = data.rows.slice(0, 5).map(row => row[i] ?? '')
      map[i] = detectField(h, samples)
    })
    return map
  })

  const preview = data.rows.slice(0, 3)

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">
        {data.rows.length} ligne(s) importées. Assignez chaque colonne CSV à un champ SJ Reviews.
      </p>

      <div className="border border-gray-200 overflow-auto">
        <table className="min-w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 w-8">#</th>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500">Colonne CSV</th>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500">Champ SJ</th>
              {preview.map((_, ri) => (
                <th key={ri} className="px-3 py-2 text-left text-xs font-medium text-gray-400">Ex. {ri + 1}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.headers.map((header, i) => (
              <tr key={i} className={columnMap[i] === '_ignore' ? 'opacity-40' : ''}>
                <td className="px-3 py-2 text-xs text-gray-400">{i + 1}</td>
                <td className="px-3 py-2 font-medium text-gray-700">{header}</td>
                <td className="px-3 py-2">
                  <select
                    className="text-xs border border-gray-200 rounded px-2 py-1 w-full"
                    value={columnMap[i] ?? '_ignore'}
                    onChange={(e) => setColumnMap(prev => ({ ...prev, [i]: e.target.value }))}
                  >
                    {SJ_FIELDS.map(f => (
                      <option key={f.value} value={f.value}>{f.label}</option>
                    ))}
                  </select>
                </td>
                {preview.map((row, ri) => (
                  <td key={ri} className="px-3 py-2 text-xs text-gray-500 max-w-32 truncate">{row[i] ?? ''}</td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ columnMap })}>Défauts <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 3 : Défauts ────────────────────────────────────────── */
function CsvStep3Defaults({ onNext, onBack }) {
  const [lieux, setLieux] = useState([])
  const [defaults, setDefaults] = useState({
    lieu_id: '', source: 'regiondo', certified: true, language: 'fr', sub_criteria_auto: true,
  })

  useEffect(() => {
    api.lieux().then(data => {
      setLieux(data)
      const first = data.find(l => l.active) ?? data[0]
      if (first) setDefaults(prev => ({ ...prev, lieu_id: first.id }))
    }).catch(() => {})
  }, [])

  const set = (key, val) => setDefaults(prev => ({ ...prev, [key]: val }))

  return (
    <div className="max-w-lg">
      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Lieu rattaché</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.lieu_id} onChange={(e) => set('lieu_id', e.target.value)}>
            <option value="">— Aucun lieu —</option>
            {lieux.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Source</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.source} onChange={(e) => set('source', e.target.value)}>
            {Object.entries(SOURCE_LABELS).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Langue par défaut</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.language} onChange={(e) => set('language', e.target.value)}>
            <option value="fr">Français</option>
            <option value="en">Anglais</option>
            <option value="it">Italien</option>
            <option value="de">Allemand</option>
            <option value="es">Espagnol</option>
          </select>
        </div>
        <div className="flex items-center justify-between py-2 border-t border-gray-100">
          <div>
            <p className="text-sm font-medium text-gray-700">Avis certifié</p>
            <p className="text-xs text-gray-500">Marquer tous les avis importés comme certifiés</p>
          </div>
          <Toggle checked={defaults.certified} onChange={(v) => set('certified', v)} />
        </div>
        <div className="flex items-center justify-between py-2 border-t border-gray-100">
          <div>
            <p className="text-sm font-medium text-gray-700">Sous-critères automatiques</p>
            <p className="text-xs text-gray-500">Hérite la note globale pour qualité/prix, ambiance, expérience, paysage</p>
          </div>
          <Toggle checked={defaults.sub_criteria_auto} onChange={(v) => set('sub_criteria_auto', v)} />
        </div>
      </div>
      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ defaults })}>Produits <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 4 : Produits ───────────────────────────────────────── */
function CsvStep4Products({ data, columnMap, onNext, onBack }) {
  const [posts, setPosts] = useState([])
  const [loadingPosts, setLoadingPosts] = useState(true)
  const [productMap, setProductMap] = useState({})

  const productColIdx = Object.entries(columnMap).filter(([, v]) => v === 'product').at(-1)?.[0]
  const uniqueProducts = useMemo(() => {
    if (productColIdx === undefined) return []
    const seen = new Set()
    data.rows.forEach(row => {
      const val = (row[productColIdx] ?? '').trim()
      if (val) seen.add(val)
    })
    return [...seen]
  }, [data.rows, productColIdx])

  useEffect(() => {
    api.importPostMatches()
      .then(setPosts)
      .catch(() => setPosts([]))
      .finally(() => setLoadingPosts(false))
  }, [])

  if (!uniqueProducts.length) {
    return (
      <div className="max-w-lg">
        <Notice type="info">Aucune colonne "Produit" mappée — étape ignorée.</Notice>
        <div className="flex justify-between mt-6">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <Btn onClick={() => onNext({ productMap: {} })}>Aperçu <IconChevronRight size={14} /></Btn>
        </div>
      </div>
    )
  }

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">Associez chaque produit à un post WordPress.</p>
      {loadingPosts ? (
        <div className="flex items-center gap-2 py-4"><Spinner size={16} /><span className="text-sm text-gray-500">Chargement des posts...</span></div>
      ) : (
        <div className="border border-gray-200 divide-y">
          {uniqueProducts.map(product => (
            <div key={product} className="flex items-center gap-4 px-4 py-3">
              <div className="flex-1 text-sm font-medium text-gray-700 min-w-0 truncate">{product}</div>
              <div className="w-px h-6 bg-gray-200" />
              <select
                className="flex-1 border border-gray-200 rounded px-3 py-1.5 text-sm"
                value={productMap[product] ?? ''}
                onChange={(e) => setProductMap(prev => ({ ...prev, [product]: e.target.value ? parseInt(e.target.value) : 0 }))}
              >
                <option value="">— Ignorer —</option>
                {posts.map(p => <option key={p.id} value={p.id}>{p.title}</option>)}
              </select>
            </div>
          ))}
        </div>
      )}
      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ productMap })}>Aperçu <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 5 : Aperçu & Import ────────────────────────────────── */
function CsvStep5Preview({ data, columnMap, defaults, productMap, onBack, onDone }) {
  const [preview, setPreview]   = useState(null)
  const [loading, setLoading]   = useState(false)
  const [importing, setImporting] = useState(false)
  const [result, setResult]     = useState(null)
  const [error, setError]       = useState('')

  const mappedRows = useMemo(() => data.rows.map(row => mapRowToSJ(row, data.headers, columnMap)), [data, columnMap])

  const runPreview = async () => {
    setLoading(true); setError('')
    try {
      setPreview(await api.importPreview({ rows: mappedRows, defaults, product_map: productMap }))
    } catch (e) { setError(e.message) }
    finally { setLoading(false) }
  }

  const runImport = async () => {
    setImporting(true); setError('')
    try {
      setResult(await api.importExecute({ rows: mappedRows, defaults, product_map: productMap }))
    } catch (e) { setError(e.message) }
    finally { setImporting(false) }
  }

  const statusClass = {
    new:       'text-green-700 bg-green-50',
    duplicate: 'text-yellow-700 bg-yellow-50',
    error:     'text-red-700 bg-red-50',
  }
  const statusLabel = { new: 'Nouveau', duplicate: 'Doublon', error: 'Erreur' }

  if (result) {
    return (
      <div className="max-w-lg text-center py-8">
        <div className="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
          <IconCheck size={24} strokeWidth={2.5} className="text-emerald-600" />
        </div>
        <h2 className="text-lg font-bold mb-2">Import terminé</h2>
        <div className="text-sm text-gray-600 space-y-1">
          <p><span className="font-semibold text-green-700">{result.imported}</span> avis importés</p>
          <p><span className="font-semibold text-yellow-600">{result.skipped}</span> doublons ignorés</p>
          {result.errors?.length > 0 && (
            <p><span className="font-semibold text-red-600">{result.errors.length}</span> erreurs</p>
          )}
        </div>
        {result.errors?.length > 0 && (
          <div className="mt-4 text-left border border-red-200 rounded p-3 bg-red-50 text-xs text-red-700 space-y-1">
            {result.errors.slice(0, 10).map((e, i) => <p key={i}>{e}</p>)}
          </div>
        )}
        <Btn className="mt-6" onClick={onDone}>Terminer l'onboarding <IconChevronRight size={14} /></Btn>
      </div>
    )
  }

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">
        {mappedRows.length} ligne(s) à traiter.{' '}
        {preview && (
          <span>
            <span className="text-green-700 font-medium">{preview.counts.new} nouveaux</span>
            {preview.counts.duplicate > 0 && <span> · <span className="text-yellow-600 font-medium">{preview.counts.duplicate} doublons</span></span>}
            {preview.counts.error > 0 && <span> · <span className="text-red-600 font-medium">{preview.counts.error} erreurs</span></span>}
          </span>
        )}
      </p>

      {error && <Notice type="error" className="mb-4">{error}</Notice>}

      {!preview ? (
        <div className="flex justify-between mt-6">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <Btn onClick={runPreview} loading={loading}>Analyser l'aperçu</Btn>
        </div>
      ) : (
        <>
          <div className="border border-gray-200 overflow-auto max-h-96">
            <table className="min-w-full text-xs">
              <thead className="bg-gray-50 sticky top-0">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Statut</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Auteur</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Note</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Produit</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">N° commande</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Raison</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {preview.rows.map((item, i) => (
                  <tr key={i} className={item.status === 'error' ? 'bg-red-50' : item.status === 'duplicate' ? 'bg-yellow-50' : ''}>
                    <td className="px-3 py-2">
                      <span className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium ${statusClass[item.status]}`}>
                        {statusLabel[item.status]}
                      </span>
                    </td>
                    <td className="px-3 py-2 font-medium">{item.row.author || '—'}</td>
                    <td className="px-3 py-2">{item.row.rating || '—'}</td>
                    <td className="px-3 py-2 max-w-32 truncate">{item.row.product || '—'}</td>
                    <td className="px-3 py-2">{item.row.order_id || '—'}</td>
                    <td className="px-3 py-2 text-gray-500">{item.reason || ''}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex justify-between items-center mt-6">
            <Btn variant="secondary" onClick={onBack}>Retour</Btn>
            <div className="flex items-center gap-3">
              <Btn variant="ghost" size="sm" onClick={runPreview} loading={loading}>Actualiser</Btn>
              <Btn onClick={runImport} disabled={importing || preview.counts.new === 0} loading={importing}>
                Importer {preview.counts.new} avis
              </Btn>
            </div>
          </div>
        </>
      )}
    </div>
  )
}

/* ═══════════════════════════════════════════════════════════════════
   STEP 4 : Terminé — summary & next actions
   ═══════════════════════════════════════════════════════════════════ */
function StepDone() {
  const navigate = useNavigate()
  const [stats, setStats] = useState(null)

  useEffect(() => {
    api.dashboard().then(setStats).catch(() => {})
  }, [])

  return (
    <div className="max-w-lg mx-auto text-center py-8">
      <div className="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
        <IconRocket size={28} className="text-emerald-600" />
      </div>
      <h2 className="text-xl font-bold mb-2">Configuration terminée</h2>
      <p className="text-sm text-gray-500 mb-6">
        Votre plugin SJ Reviews est prêt. Voici ce que vous pouvez faire ensuite :
      </p>

      {stats && (
        <div className="flex justify-center gap-6 mb-8">
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.total ?? 0}</p>
            <p className="text-xs text-gray-500">Avis</p>
          </div>
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.avg ? Number(stats.avg).toFixed(1) : '—'}</p>
            <p className="text-xs text-gray-500">Note moyenne</p>
          </div>
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.sources ? Object.keys(stats.sources).length : 0}</p>
            <p className="text-xs text-gray-500">Sources</p>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
        <ActionTile
          icon={<IconStar size={18} />}
          title="Voir les avis"
          description="Gérez et modérez vos avis importés."
          onClick={() => navigate('/reviews')}
        />
        <ActionTile
          icon={<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>}
          title="Dashboard"
          description="Statistiques et tendances en temps réel."
          onClick={() => navigate('/')}
        />
        <ActionTile
          icon={<IconMapPin size={18} />}
          title="Gérer les lieux"
          description="Lancer une synchronisation Google/Trustpilot."
          onClick={() => navigate('/lieux')}
        />
        <ActionTile
          icon={<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>}
          title="Réglages avancés"
          description="Apparence, critères, shortcodes et plus."
          onClick={() => navigate('/settings/display')}
        />
      </div>
    </div>
  )
}

function ActionTile({ icon, title, description, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex items-start gap-3 border border-gray-200 rounded-lg p-4 text-left hover:border-gray-300 hover:bg-gray-50/50 transition-colors"
    >
      <span className="text-gray-500 mt-0.5">{icon}</span>
      <div>
        <p className="text-sm font-semibold text-gray-900">{title}</p>
        <p className="text-xs text-gray-500">{description}</p>
      </div>
    </button>
  )
}

// Import IconStar for the done step
function IconStar2({ size = 16, ...props }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" {...props}>
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>
  )
}

/* ═══════════════════════════════════════════════════════════════════
   MAIN COMPONENT
   ═══════════════════════════════════════════════════════════════════ */
export default function Import() {
  const [step, setStep] = useState(0)
  const [completedSteps, setCompletedSteps] = useState([])

  const markDone = (stepId) => {
    setCompletedSteps(prev => prev.includes(stepId) ? prev : [...prev, stepId])
  }

  const goNext = (currentStepId) => {
    markDone(currentStepId)
    setStep(s => Math.min(s + 1, ONBOARDING_STEPS.length - 1))
  }

  return (
    <div>
      <PageHeader
        title="Onboarding"
        subtitle="Configurez SJ Reviews en quelques étapes"
      />

      <div className="px-8 py-6">
        <OnboardingNav
          current={step}
          onGo={setStep}
          completedSteps={completedSteps}
        />

        {step === 0 && (
          <StepSources
            onNext={() => goNext('sources')}
            onSkip={() => goNext('sources')}
          />
        )}
        {step === 1 && (
          <StepLieux
            onNext={() => goNext('lieux')}
            onBack={() => setStep(0)}
          />
        )}
        {step === 2 && (
          <StepImport
            onNext={() => goNext('import')}
            onBack={() => setStep(1)}
          />
        )}
        {step === 3 && (
          <StepDone />
        )}
      </div>
    </div>
  )
}
