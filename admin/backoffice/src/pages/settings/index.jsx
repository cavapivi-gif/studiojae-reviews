import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { api } from '../../lib/api'
import { PageHeader, Btn, Spinner } from '../../components/ui'
import { IconCheck } from '../../components/Icons'
import { useToast } from '../../components/Toast'
import TabApi from './TabApi'
import TabDisplay from './TabDisplay'
import TabCriteria from './TabCriteria'
import TabLinks from './TabLinks'
import TabShortcodes from './TabShortcodes'

const DEFAULTS = {
  default_layout:      'slider-i',
  default_preset:      'minimal',
  star_color:          '#f5a623',
  certified_label:     'Certifié',
  max_front:           '5',
  google_api_key:      '',
  trustpilot_api_key:  '',
  tripadvisor_api_key: '',
  anthropic_api_key:   '',
  linked_post_types:   [],
  sync_frequency:      'off',
  criteria_labels:     { qualite_prix: 'Qualité/prix', ambiance: 'Ambiance', experience: 'Expérience', paysage: 'Paysage' },
  rating_labels:       { '5': 'Excellent', '4': 'Bien', '3': 'Moyen', '2': 'Médiocre', '1': 'Horrible' },
  bubble_color:        '#34d399',
  text_words:          '40',
  autoplay_delay:      '4000',
  email_digest_enabled: '0',
  email_digest_email:  '',
  toast_enabled:       '0',
  toast_position:      'bottom-left',
  toast_delay:         '5000',
  toast_reviews_url:   '',
}

const TABS = [
  { id: 'api',        label: 'API & Sync' },
  { id: 'display',    label: 'Affichage' },
  { id: 'criteria',   label: 'Critères' },
  { id: 'links',      label: 'Liaisons' },
  { id: 'shortcodes', label: 'Shortcodes' },
]

/**
 * Page Réglages — gère l'état global et délègue le rendu de chaque onglet.
 */
export default function Settings() {
  const toast = useToast()
  const { tab: urlTab } = useParams()
  const navigate = useNavigate()
  const [form, setForm]             = useState(DEFAULTS)
  const [loading, setLoading]       = useState(true)
  const [saving, setSaving]         = useState(false)
  const validTabs = TABS.map(t => t.id)
  const activeTab = validTabs.includes(urlTab) ? urlTab : 'api'
  const setActiveTab = (id) => navigate(`/settings/${id}`, { replace: true })
  const [availablePostTypes, setAvailablePostTypes] = useState([])

  // États de test des clés API
  const [googleKeyStatus, setGoogleKeyStatus]           = useState(null)
  const [googleKeyMsg, setGoogleKeyMsg]                 = useState('')
  const [trustpilotKeyStatus, setTrustpilotKeyStatus]   = useState(null)
  const [trustpilotKeyMsg, setTrustpilotKeyMsg]         = useState('')
  const [tripadvisorKeyStatus, setTripadvisorKeyStatus] = useState(null)
  const [tripadvisorKeyMsg, setTripadvisorKeyMsg]       = useState('')
  const [anthropicKeyStatus, setAnthropicKeyStatus]     = useState(null)
  const [anthropicKeyMsg, setAnthropicKeyMsg]           = useState('')
  const [aiSummaryLoading, setAiSummaryLoading]         = useState(false)
  const [digestTestLoading, setDigestTestLoading]       = useState(false)

  useEffect(() => {
    Promise.all([api.settings(), api.postTypes()])
      .then(([s, pts]) => {
        setForm({
          ...DEFAULTS,
          ...s,
          linked_post_types: Array.isArray(s.linked_post_types) ? s.linked_post_types : [],
          criteria_labels: { ...DEFAULTS.criteria_labels, ...(s.criteria_labels || {}) },
          rating_labels: { ...DEFAULTS.rating_labels, ...(s.rating_labels || {}) },
        })
        setAvailablePostTypes(pts)
      })
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [])

  /** Curried setter — réinitialise aussi le statut de test de la clé modifiée. */
  const set = key => value => {
    setForm(f => ({ ...f, [key]: value }))
    if (key === 'google_api_key') setGoogleKeyStatus(null)
    if (key === 'trustpilot_api_key') setTrustpilotKeyStatus(null)
    if (key === 'tripadvisor_api_key') setTripadvisorKeyStatus(null)
    if (key === 'anthropic_api_key') { setAnthropicKeyStatus(null); setAiSummaryLoading(false) }
  }

  const setCriteriaLabel = key => e => {
    setForm(f => ({ ...f, criteria_labels: { ...f.criteria_labels, [key]: e.target.value } }))
  }

  const setRatingLabel = key => e => {
    setForm(f => ({ ...f, rating_labels: { ...f.rating_labels, [key]: e.target.value } }))
  }

  async function handleSave(e) {
    e?.preventDefault()
    setSaving(true)
    try {
      await api.saveSettings(form)
      toast.success('Réglages enregistrés.')
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  async function testGoogleKey() {
    if (!form.google_api_key.trim()) return
    setGoogleKeyStatus('testing')
    try {
      const res = await api.testGoogleKey(form.google_api_key)
      setGoogleKeyStatus(res.ok ? 'ok' : 'error')
      setGoogleKeyMsg(res.message ?? '')
    } catch (e) { setGoogleKeyStatus('error'); setGoogleKeyMsg(e.message) }
  }

  async function testTrustpilotKey() {
    if (!form.trustpilot_api_key.trim()) return
    setTrustpilotKeyStatus('testing')
    try {
      const res = await api.testTrustpilotKey(form.trustpilot_api_key)
      setTrustpilotKeyStatus(res.ok ? 'ok' : 'error')
      setTrustpilotKeyMsg(res.message ?? '')
    } catch (e) { setTrustpilotKeyStatus('error'); setTrustpilotKeyMsg(e.message) }
  }

  async function testTripadvisorKey() {
    if (!form.tripadvisor_api_key.trim()) return
    setTripadvisorKeyStatus('testing')
    try {
      const res = await api.testTripadvisorKey(form.tripadvisor_api_key)
      setTripadvisorKeyStatus(res.ok ? 'ok' : 'error')
      setTripadvisorKeyMsg(res.message ?? '')
    } catch (e) { setTripadvisorKeyStatus('error'); setTripadvisorKeyMsg(e.message) }
  }

  async function testAnthropicKey() {
    if (!form.anthropic_api_key.trim()) return
    setAnthropicKeyStatus('testing')
    try {
      const res = await api.testAnthropicKey(form.anthropic_api_key)
      setAnthropicKeyStatus(res.ok ? 'ok' : 'error')
      setAnthropicKeyMsg(res.message ?? '')
    } catch (e) { setAnthropicKeyStatus('error'); setAnthropicKeyMsg(e.message) }
  }

  if (loading) return (
    <div>
      <PageHeader title="Réglages" />
      <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
    </div>
  )

  const SaveBtn = ({ className = '' }) => (
    <Btn size="sm" loading={saving} onClick={handleSave} className={className}>
      <IconCheck size={13} strokeWidth={2} />
      Enregistrer
    </Btn>
  )

  return (
    <div>
      <PageHeader title="Réglages" actions={<SaveBtn />} />

      {/* Tabs */}
      <div className="border-b border-gray-200 px-8">
        <nav className="flex gap-0 -mb-px" role="tablist">
          {TABS.map(tab => (
            <button
              key={tab.id}
              role="tab"
              aria-selected={activeTab === tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`px-4 py-3 text-xs font-medium border-b-2 transition-colors
                ${activeTab === tab.id
                  ? 'border-black text-black'
                  : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300'
                }`}
            >
              {tab.label}
            </button>
          ))}
        </nav>
      </div>

      <form onSubmit={handleSave} className="px-8 py-6 max-w-xl">

        {activeTab === 'api' && (
          <TabApi
            form={form} set={set}
            googleKeyStatus={googleKeyStatus} googleKeyMsg={googleKeyMsg}
            trustpilotKeyStatus={trustpilotKeyStatus} trustpilotKeyMsg={trustpilotKeyMsg}
            tripadvisorKeyStatus={tripadvisorKeyStatus} tripadvisorKeyMsg={tripadvisorKeyMsg}
            anthropicKeyStatus={anthropicKeyStatus} anthropicKeyMsg={anthropicKeyMsg}
            aiSummaryLoading={aiSummaryLoading} digestTestLoading={digestTestLoading}
            handleSave={handleSave}
            testGoogleKey={testGoogleKey}
            testTrustpilotKey={testTrustpilotKey}
            testTripadvisorKey={testTripadvisorKey}
            testAnthropicKey={testAnthropicKey}
            setAiSummaryLoading={setAiSummaryLoading}
            setDigestTestLoading={setDigestTestLoading}
          />
        )}

        {activeTab === 'display' && (
          <TabDisplay form={form} set={set} />
        )}

        {activeTab === 'criteria' && (
          <TabCriteria
            form={form}
            setCriteriaLabel={setCriteriaLabel}
            setRatingLabel={setRatingLabel}
            DEFAULTS={DEFAULTS}
          />
        )}

        {activeTab === 'links' && (
          <TabLinks
            form={form}
            setForm={setForm}
            availablePostTypes={availablePostTypes}
          />
        )}

        {activeTab === 'shortcodes' && (
          <TabShortcodes />
        )}

        {/* Bouton save sticky en bas */}
        <div className="sticky bottom-0 bg-white border-t border-gray-200 -mx-8 px-8 py-3 mt-8 flex justify-end">
          <SaveBtn />
        </div>
      </form>
    </div>
  )
}
