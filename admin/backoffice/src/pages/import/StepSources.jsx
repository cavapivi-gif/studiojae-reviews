import { useState, useEffect } from 'react'
import { api } from '../../lib/api'
import { Btn, Spinner } from '../../components/ui'
import { IconCheck, IconChevronRight } from '../../components/Icons'
import { useToast } from '../../components/Toast'

/* ═══════════════════════════════════════════════════════════════════
   STEP 1 : Sources API — inline configuration
   ═══════════════════════════════════════════════════════════════════ */
export default function StepSources({ onNext, onSkip }) {
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
