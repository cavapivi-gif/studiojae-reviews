import { useState, useEffect } from 'react'
import { api } from '../lib/api'
import { PageHeader, Input, Select, Btn, Spinner, Toggle } from '../components/ui'
import { IconCheck } from '../components/Icons'
import { useToast } from '../components/Toast'

const DEFAULTS = {
  default_layout:      'slider-i',
  default_preset:      'minimal',
  star_color:          '#f5a623',
  certified_label:     'Certifié',
  max_front:           '5',
  google_api_key:      '',
  trustpilot_api_key:  '',
  tripadvisor_api_key: '',
  linked_post_types:   [],
  sync_frequency:      'off',
  criteria_labels:     { qualite_prix: 'Qualité/prix', ambiance: 'Ambiance', experience: 'Expérience', paysage: 'Paysage' },
  bubble_color:        '#34d399',
  text_words:          '40',
  autoplay_delay:      '4000',
}

const TABS = [
  { id: 'api',        label: 'API & Sync' },
  { id: 'display',    label: 'Affichage' },
  { id: 'criteria',   label: 'Critères' },
  { id: 'links',      label: 'Liaisons' },
  { id: 'shortcodes', label: 'Shortcodes' },
]

function Pill({ status, children }) {
  const cls = {
    ok:    'bg-emerald-50 border-emerald-200 text-emerald-700',
    error: 'bg-red-50 border-red-200 text-red-700',
  }[status] ?? 'bg-gray-50 border-gray-200 text-gray-600'
  return <div className={`flex items-start gap-2 text-xs border px-3 py-2 ${cls}`}>{children}</div>
}

function Tutorial({ title, children }) {
  const [open, setOpen] = useState(false)
  return (
    <div className={`mt-2 border transition-all overflow-hidden ${open ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-gray-50/60'}`}>
      <button type="button" onClick={() => setOpen(o => !o)} className="w-full flex items-center justify-between px-3 py-2 text-left">
        <span className="flex items-center gap-1.5 text-xs font-semibold text-indigo-600">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none" className="shrink-0"><circle cx="6.5" cy="6.5" r="6" stroke="currentColor" strokeWidth="1.2"/><path d="M6.5 5.5v3M6.5 4h.01" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round"/></svg>
          {title}
        </span>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" className={`shrink-0 text-indigo-400 transition-transform duration-200 ${open ? 'rotate-180' : ''}`}><path d="M3 5l4 4 4-4" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/></svg>
      </button>
      {open && <div className="px-3 pb-3 text-xs text-gray-600 space-y-1.5 border-t border-indigo-100 pt-2">{children}</div>}
    </div>
  )
}

function SectionHeader({ children, badge }) {
  return (
    <div className="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
      <span className="text-xs font-bold text-gray-500 uppercase tracking-widest">{children}</span>
      {badge && <span className="text-[10px] font-semibold bg-indigo-100 text-indigo-600 px-2 py-0.5">{badge}</span>}
    </div>
  )
}

function ApiKeyField({ label, value, onChange, onTest, testStatus, testMsg, badge, tutorial }) {
  return (
    <div className="flex flex-col gap-3">
      <div className="flex gap-2 items-end">
        <div className="flex-1">
          <Input label={label} type="password" value={value} onChange={e => onChange(e.target.value)} placeholder="Clé API…" autoComplete="off" />
        </div>
        <Btn type="button" variant="ghost" size="sm" onClick={onTest} loading={testStatus === 'testing'} disabled={!value.trim() || testStatus === 'testing'} style={{ marginBottom: '1px' }}>
          Tester
        </Btn>
      </div>
      {testStatus === 'ok' && <Pill status="ok"><IconCheck size={12} strokeWidth={2.5} className="mt-0.5 shrink-0" /><span>Clé valide.{testMsg ? ` ${testMsg}` : ''}</span></Pill>}
      {testStatus === 'error' && <Pill status="error"><span>{testMsg || 'Clé invalide.'}</span></Pill>}
      {tutorial}
    </div>
  )
}

export default function Settings() {
  const toast = useToast()
  const [form, setForm]             = useState(DEFAULTS)
  const [loading, setLoading]       = useState(true)
  const [saving, setSaving]         = useState(false)
  const [activeTab, setActiveTab]   = useState('api')
  const [availablePostTypes, setAvailablePostTypes] = useState([])

  // API key test states
  const [googleKeyStatus, setGoogleKeyStatus]         = useState(null)
  const [googleKeyMsg, setGoogleKeyMsg]               = useState('')
  const [trustpilotKeyStatus, setTrustpilotKeyStatus] = useState(null)
  const [trustpilotKeyMsg, setTrustpilotKeyMsg]       = useState('')
  const [tripadvisorKeyStatus, setTripadvisorKeyStatus] = useState(null)
  const [tripadvisorKeyMsg, setTripadvisorKeyMsg]       = useState('')

  useEffect(() => {
    Promise.all([api.settings(), api.postTypes()])
      .then(([s, pts]) => {
        setForm({
          ...DEFAULTS,
          ...s,
          linked_post_types: Array.isArray(s.linked_post_types) ? s.linked_post_types : [],
          criteria_labels: { ...DEFAULTS.criteria_labels, ...(s.criteria_labels || {}) },
        })
        setAvailablePostTypes(pts)
      })
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [])

  const set = key => value => {
    setForm(f => ({ ...f, [key]: value }))
    if (key === 'google_api_key') setGoogleKeyStatus(null)
    if (key === 'trustpilot_api_key') setTrustpilotKeyStatus(null)
    if (key === 'tripadvisor_api_key') setTripadvisorKeyStatus(null)
  }

  const setCriteriaLabel = key => e => {
    setForm(f => ({ ...f, criteria_labels: { ...f.criteria_labels, [key]: e.target.value } }))
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

        {/* ── TAB: API & Sync ─────────────────────────── */}
        {activeTab === 'api' && (
          <div className="flex flex-col gap-7">
            <section>
              <SectionHeader badge="Google Places">Google Maps API</SectionHeader>
              <ApiKeyField
                label="Clé API Google Maps"
                value={form.google_api_key}
                onChange={set('google_api_key')}
                onTest={testGoogleKey}
                testStatus={googleKeyStatus}
                testMsg={googleKeyMsg}
                tutorial={
                  <Tutorial title="Comment configurer la clé API Google ?">
                    <p><strong>1.</strong> Allez sur <strong>console.cloud.google.com</strong> → Bibliothèque → activez <strong>Places API</strong>.</p>
                    <p><strong>2.</strong> Identifiants → Créer des identifiants → Clé API.</p>
                    <p><strong>3.</strong> Restrictions recommandées pour cette clé (serveur) :</p>
                    <ul className="ml-3 space-y-0.5 list-disc">
                      <li><strong>Aucune restriction HTTP referrer</strong> — l'import s'effectue depuis votre serveur.</li>
                    </ul>
                  </Tutorial>
                }
              />
            </section>

            <section>
              <SectionHeader badge="Trustpilot">Trustpilot Business API</SectionHeader>
              <ApiKeyField
                label="Clé API Trustpilot"
                value={form.trustpilot_api_key}
                onChange={set('trustpilot_api_key')}
                onTest={testTrustpilotKey}
                testStatus={trustpilotKeyStatus}
                testMsg={trustpilotKeyMsg}
                tutorial={
                  <Tutorial title="Comment obtenir une clé API Trustpilot ?">
                    <p><strong>1.</strong> Créez un compte sur <strong>Trustpilot Business</strong>.</p>
                    <p><strong>2.</strong> Allez dans <strong>Integrations → API</strong>.</p>
                    <p><strong>3.</strong> Copiez votre <em>API Key</em> (pas le secret).</p>
                    <p className="text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1">
                      Le domaine Trustpilot se configure par lieu dans <strong>Lieux & Sources</strong>.
                    </p>
                  </Tutorial>
                }
              />
            </section>

            <section>
              <SectionHeader badge="TripAdvisor">TripAdvisor Content API</SectionHeader>
              <ApiKeyField
                label="Clé API TripAdvisor"
                value={form.tripadvisor_api_key}
                onChange={set('tripadvisor_api_key')}
                onTest={testTripadvisorKey}
                testStatus={tripadvisorKeyStatus}
                testMsg={tripadvisorKeyMsg}
                tutorial={
                  <Tutorial title="Comment obtenir une clé API TripAdvisor ?">
                    <p><strong>1.</strong> Inscrivez-vous sur <strong>tripadvisor.com/developers</strong>.</p>
                    <p><strong>2.</strong> Créez une application et obtenez votre clé API.</p>
                    <p>Le <em>Location ID</em> se configure par lieu dans <strong>Lieux & Sources</strong>.</p>
                  </Tutorial>
                }
              />
            </section>

            <section>
              <SectionHeader>Cache</SectionHeader>
              <p className="text-xs text-gray-500 mb-3">
                Vide le cache du dashboard (stats, tendances, répartitions). Utile après un import ou une sync manuelle.
              </p>
              <Btn type="button" variant="secondary" size="sm" onClick={async () => {
                try {
                  await api.flushCache()
                  toast.success('Cache vidé avec succès.')
                } catch (e) {
                  toast.error(e.message)
                }
              }}>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Vider le cache
              </Btn>
            </section>

            <section>
              <SectionHeader>Synchronisation automatique</SectionHeader>
              <Select
                label="Fréquence de sync automatique"
                value={form.sync_frequency}
                onChange={e => set('sync_frequency')(e.target.value)}
              >
                <option value="off">Désactivée</option>
                <option value="twice_daily">2× par jour</option>
                <option value="daily">1× par jour</option>
                <option value="weekly">1× par semaine</option>
                <option value="monthly">1× par mois</option>
              </Select>
              <p className="text-xs text-gray-400 mt-2">
                Synchronise automatiquement les notes et nombres d'avis de tous les lieux actifs (Google, Trustpilot, TripAdvisor).
              </p>
              {form.last_sync && (
                <p className="text-xs text-gray-500 mt-1">
                  Dernière sync : <strong>{form.last_sync}</strong>
                </p>
              )}
            </section>
          </div>
        )}

        {/* ── TAB: Affichage ──────────────────────────── */}
        {activeTab === 'display' && (
          <div className="flex flex-col gap-7">
            <section>
              <SectionHeader>Layout par défaut</SectionHeader>
              <div className="grid grid-cols-2 gap-4">
                <Select label="Layout" value={form.default_layout} onChange={e => set('default_layout')(e.target.value)}>
                  <option value="slider-i">Slider I</option>
                  <option value="slider-ii">Slider II (33/66)</option>
                  <option value="badge">Badge</option>
                  <option value="grid">Grille</option>
                  <option value="list">Liste</option>
                </Select>
                <Select label="Preset de style" value={form.default_preset} onChange={e => set('default_preset')(e.target.value)}>
                  <option value="minimal">Minimal</option>
                  <option value="dark">Dark</option>
                  <option value="white">White</option>
                </Select>
                <Input label="Nombre max d'avis (front)" type="number" min="1" max="20" value={form.max_front} onChange={e => set('max_front')(e.target.value)} />
                <Input label="Mots avant troncature" type="number" min="10" max="200" value={form.text_words} onChange={e => set('text_words')(e.target.value)} />
              </div>
            </section>

            <section>
              <SectionHeader>Apparence</SectionHeader>
              <div className="grid grid-cols-2 gap-4">
                <label className="flex flex-col gap-1">
                  <span className="text-xs text-gray-500">Couleur des étoiles</span>
                  <div className="flex items-center gap-2">
                    <input type="color" value={form.star_color} onChange={e => set('star_color')(e.target.value)} className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5" />
                    <code className="text-xs text-gray-400">{form.star_color}</code>
                  </div>
                </label>
                <label className="flex flex-col gap-1">
                  <span className="text-xs text-gray-500">Couleur des bulles (rating)</span>
                  <div className="flex items-center gap-2">
                    <input type="color" value={form.bubble_color} onChange={e => set('bubble_color')(e.target.value)} className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5" />
                    <code className="text-xs text-gray-400">{form.bubble_color}</code>
                  </div>
                </label>
                <Input label="Label badge certifié" value={form.certified_label} onChange={e => set('certified_label')(e.target.value)} placeholder="Certifié" />
                <Input label="Délai autoplay slider (ms)" type="number" min="1000" max="15000" step="500" value={form.autoplay_delay} onChange={e => set('autoplay_delay')(e.target.value)} />
              </div>
            </section>
          </div>
        )}

        {/* ── TAB: Critères ──────────────────────────── */}
        {activeTab === 'criteria' && (
          <div className="flex flex-col gap-7">
            <section>
              <SectionHeader>Labels des sous-critères</SectionHeader>
              <p className="text-xs text-gray-400 mb-4">
                Personnalisez les noms des sous-critères affichés dans les widgets et shortcodes.
              </p>
              <div className="grid grid-cols-2 gap-4">
                {Object.entries(form.criteria_labels).map(([key, label]) => (
                  <Input
                    key={key}
                    label={<span className="font-mono text-gray-400">{key}</span>}
                    value={label}
                    onChange={setCriteriaLabel(key)}
                    placeholder={DEFAULTS.criteria_labels[key]}
                  />
                ))}
              </div>
            </section>
          </div>
        )}

        {/* ── TAB: Liaisons ──────────────────────────── */}
        {activeTab === 'links' && (
          <div className="flex flex-col gap-7">
            <section>
              <SectionHeader>Liaison avis ↔ Post</SectionHeader>
              <p className="text-xs text-gray-500 mb-3">
                Sélectionnez les types de contenu auxquels un avis peut être lié.
                Une meta box <strong>« Lieu SJ Reviews »</strong> apparaîtra sur ces contenus.
              </p>
              {availablePostTypes.length === 0 ? (
                <p className="text-xs text-gray-400 italic">Aucun post type public trouvé.</p>
              ) : (
                <div className="flex flex-col gap-2">
                  {availablePostTypes.map(pt => {
                    const checked = form.linked_post_types.includes(pt.slug)
                    return (
                      <label
                        key={pt.slug}
                        className={`flex items-center gap-3 cursor-pointer px-3 py-2 border transition-all duration-150
                          ${checked ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200'}`}
                      >
                        <div className={`w-4 h-4 border-2 flex items-center justify-center transition-all
                          ${checked ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white'}`}>
                          {checked && <IconCheck size={10} strokeWidth={3} className="text-white" />}
                        </div>
                        <input type="checkbox" checked={checked} onChange={() => {
                          const next = checked
                            ? form.linked_post_types.filter(s => s !== pt.slug)
                            : [...form.linked_post_types, pt.slug]
                          setForm(f => ({ ...f, linked_post_types: next }))
                        }} className="sr-only" />
                        <span className="text-sm font-medium text-gray-700">{pt.label}</span>
                        <code className="text-xs text-gray-400 font-mono ml-auto">{pt.slug}</code>
                      </label>
                    )
                  })}
                </div>
              )}
              <Tutorial title="Comment fonctionne la liaison bidirectionnelle ?">
                <p><strong>Depuis l'avis :</strong> choisissez le <em>Post lié</em> dans le formulaire → l'avis est associé à ce post.</p>
                <p><strong>Depuis le post :</strong> la meta box <em>Lieu SJ Reviews</em> (dans la sidebar d'édition) vous permet de sélectionner le lieu correspondant.</p>
                <p><strong>Widget Résumé Avis :</strong> en mode <em>Auto</em>, il lit le lieu du post et filtre les statistiques automatiquement.</p>
              </Tutorial>
            </section>
          </div>
        )}

        {/* ── TAB: Shortcodes ────────────────────────── */}
        {activeTab === 'shortcodes' && (
          <div className="flex flex-col gap-7">
            <section>
              <SectionHeader>Shortcodes disponibles</SectionHeader>
              <div className="bg-gray-50 border border-gray-200 px-4 py-3 text-xs font-mono text-gray-600 space-y-1">
                <div className="text-gray-400 mb-2">{/* Avis (liste/slider) */}</div>
                <div>[sj_reviews layout="slider-i" preset="minimal" max="5"]</div>
                <div>[sj_reviews layout="grid" preset="white" columns="3"]</div>
                <div>[sj_reviews lieu_id="lieu_xxxxxxxx"]</div>
                <div className="text-gray-400 mt-3 mb-1">{/* Résumé statistique */}</div>
                <div>[sj_summary]</div>
                <div>[sj_summary lieu_id="lieu_xxxxxxxx" show_distribution="1" show_criteria="1"]</div>
                <div className="text-gray-400 mt-3 mb-1">{/* Badge de note */}</div>
                <div>[sj_rating design="card" lieu_id="all"]</div>
                <div className="text-gray-400 mt-3 mb-1">{/* Note inline */}</div>
                <div>[sj_inline_rating show_stars="1" show_score="1"]</div>
              </div>
            </section>
          </div>
        )}

        {/* Sticky save button bottom */}
        <div className="sticky bottom-0 bg-white border-t border-gray-200 -mx-8 px-8 py-3 mt-8 flex justify-end">
          <SaveBtn />
        </div>
      </form>
    </div>
  )
}
