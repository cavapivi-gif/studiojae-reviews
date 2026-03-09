import { useState, useEffect } from 'react'
import { api } from '../lib/api'
import { PageHeader, Input, Select, Btn, Notice, Spinner } from '../components/ui'
import { IconCheck } from '../components/Icons'

const DEFAULTS = {
  default_layout:    'slider-i',
  default_preset:    'minimal',
  star_color:        '#f5a623',
  certified_label:   'Certifié',
  max_front:         '5',
  google_api_key:    '',
  linked_post_types: [],
}

/* ── Tutorial reveal ──────────────────────────────────────────── */
function Tutorial({ title, children }) {
  const [open, setOpen] = useState(false)
  return (
    <div className={`mt-2 rounded-lg border transition-all duration-200 overflow-hidden ${open ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-gray-50/60'}`}>
      <button
        type="button"
        onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between px-3 py-2 text-left group"
      >
        <span className="flex items-center gap-1.5 text-xs font-semibold text-indigo-600">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none" className="shrink-0">
            <circle cx="6.5" cy="6.5" r="6" stroke="currentColor" strokeWidth="1.2"/>
            <path d="M6.5 5.5v3M6.5 4h.01" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round"/>
          </svg>
          {title}
        </span>
        <svg
          width="14" height="14" viewBox="0 0 14 14" fill="none"
          className={`shrink-0 text-indigo-400 transition-transform duration-200 ${open ? 'rotate-180' : ''}`}
        >
          <path d="M3 5l4 4 4-4" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      </button>
      {open && (
        <div className="px-3 pb-3 text-xs text-gray-600 space-y-1.5 border-t border-indigo-100 pt-2">
          {children}
        </div>
      )}
    </div>
  )
}

/* ── Section header ───────────────────────────────────────────── */
function SectionHeader({ children, badge }) {
  return (
    <div className="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
      <span className="text-xs font-bold text-gray-500 uppercase tracking-widest">{children}</span>
      {badge && <span className="text-[10px] font-semibold bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">{badge}</span>}
    </div>
  )
}

/* ── Status pill ──────────────────────────────────────────────── */
function Pill({ status, children }) {
  const cls = {
    ok:      'bg-emerald-50 border-emerald-200 text-emerald-700',
    error:   'bg-red-50 border-red-200 text-red-700',
    warning: 'bg-amber-50 border-amber-200 text-amber-700',
  }[status] ?? 'bg-gray-50 border-gray-200 text-gray-600'
  return (
    <div className={`flex items-start gap-2 text-xs border px-3 py-2 rounded-lg ${cls}`}>
      {children}
    </div>
  )
}

export default function Settings() {
  const [form, setForm]             = useState(DEFAULTS)
  const [loading, setLoading]       = useState(true)
  const [saving, setSaving]         = useState(false)
  const [saved, setSaved]           = useState(false)
  const [error, setError]           = useState(null)
  const [keyStatus, setKeyStatus]   = useState(null) // null | 'testing' | 'ok' | 'error'
  const [keyMsg, setKeyMsg]         = useState('')
  const [availablePostTypes, setAvailablePostTypes] = useState([])

  useEffect(() => {
    Promise.all([api.settings(), api.postTypes()])
      .then(([s, pts]) => {
        setForm({ ...DEFAULTS, ...s, linked_post_types: Array.isArray(s.linked_post_types) ? s.linked_post_types : [] })
        setAvailablePostTypes(pts)
      })
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  const set = key => value => {
    setForm(f => ({ ...f, [key]: value }))
    if (key === 'google_api_key') setKeyStatus(null)
  }

  async function handleSave(e) {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setSaved(false)
    try {
      await api.saveSettings(form)
      setSaved(true)
      setTimeout(() => setSaved(false), 3000)
    } catch (e) {
      setError(e.message)
    } finally {
      setSaving(false)
    }
  }

  async function testApiKey() {
    if (!form.google_api_key.trim()) return
    setKeyStatus('testing')
    setKeyMsg('')
    try {
      const res = await api.testGoogleKey(form.google_api_key)
      setKeyStatus(res.ok ? 'ok' : 'error')
      setKeyMsg(res.message ?? '')
    } catch (e) {
      setKeyStatus('error')
      setKeyMsg(e.message)
    }
  }

  if (loading) return (
    <div>
      <PageHeader title="Réglages" />
      <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
    </div>
  )

  return (
    <div>
      <PageHeader
        title="Réglages"
        actions={
          <Btn size="sm" loading={saving} onClick={handleSave}>
            <IconCheck size={13} strokeWidth={2} />
            Enregistrer
          </Btn>
        }
      />

      <form onSubmit={handleSave} className="px-8 py-6 max-w-xl">
        {error && (
          <div className="mb-4 animate-in slide-in-from-top-1 duration-200">
            <Notice type="error">{error}</Notice>
          </div>
        )}
        {saved && (
          <div className="mb-4 animate-in slide-in-from-top-1 duration-200">
            <Notice type="success">
              <span className="flex items-center gap-1.5">
                <IconCheck size={12} strokeWidth={2.5} /> Réglages enregistrés.
              </span>
            </Notice>
          </div>
        )}

        <div className="flex flex-col gap-7">

          {/* ── Google Maps API ─────────────────────────────── */}
          <section>
            <SectionHeader badge="Requis pour l'import">Google Maps API</SectionHeader>
            <div className="flex flex-col gap-3">
              <div className="flex gap-2 items-end">
                <div className="flex-1">
                  <Input
                    label="Clé API Google Maps"
                    type="password"
                    value={form.google_api_key}
                    onChange={e => set('google_api_key')(e.target.value)}
                    placeholder="AIzaSy…"
                    autoComplete="off"
                  />
                </div>
                <Btn
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={testApiKey}
                  loading={keyStatus === 'testing'}
                  disabled={!form.google_api_key.trim() || keyStatus === 'testing'}
                  style={{ marginBottom: '1px' }}
                >
                  Tester
                </Btn>
              </div>

              {keyStatus === 'ok' && (
                <Pill status="ok">
                  <IconCheck size={12} strokeWidth={2.5} className="mt-0.5 shrink-0" />
                  <span>Clé valide — Places API accessible.{keyMsg ? ` ${keyMsg}` : ''}</span>
                </Pill>
              )}
              {keyStatus === 'error' && (
                <Pill status="error">
                  <span>❌ {keyMsg || 'Clé invalide ou Places API non activée.'}</span>
                </Pill>
              )}

              <Tutorial title="Comment configurer la clé API Google ?">
                <p><strong>1.</strong> Allez sur <strong>console.cloud.google.com</strong> → Bibliothèque → activez <strong>Places API</strong>.</p>
                <p><strong>2.</strong> Identifiants → Créer des identifiants → Clé API.</p>
                <p><strong>3.</strong> Restrictions recommandées pour cette clé (serveur) :</p>
                <ul className="ml-3 space-y-0.5 list-disc">
                  <li><strong>Aucune restriction HTTP referrer</strong> — l'import s'effectue depuis votre serveur.</li>
                  <li>Ou ajoutez votre domaine avec le format <code className="bg-white/80 px-1 rounded">https://votre-site.com/*</code></li>
                </ul>
                <p className="text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded">
                  ⚠️ Si l'import échoue avec une restriction active, c'est souvent un problème de format de domaine dans Cloud Console.
                </p>
              </Tutorial>
            </div>
          </section>

          {/* ── Affichage par défaut ─────────────────────────── */}
          <section>
            <SectionHeader>Affichage par défaut</SectionHeader>
            <div className="grid grid-cols-2 gap-4">
              <Select
                label="Layout par défaut"
                value={form.default_layout}
                onChange={e => set('default_layout')(e.target.value)}
              >
                <option value="slider-i">Slider I</option>
                <option value="slider-ii">Slider II (33/66)</option>
                <option value="badge">Badge</option>
                <option value="grid">Grille</option>
                <option value="list">Liste</option>
              </Select>
              <Select
                label="Preset de style"
                value={form.default_preset}
                onChange={e => set('default_preset')(e.target.value)}
              >
                <option value="minimal">Minimal</option>
                <option value="dark">Dark</option>
                <option value="white">White</option>
              </Select>
              <Input
                label="Nombre max d'avis (front)"
                type="number"
                min="1"
                max="20"
                value={form.max_front}
                onChange={e => set('max_front')(e.target.value)}
              />
            </div>
          </section>

          {/* ── Personnalisation ─────────────────────────────── */}
          <section>
            <SectionHeader>Personnalisation</SectionHeader>
            <div className="grid grid-cols-2 gap-4">
              <label className="flex flex-col gap-1">
                <span className="text-xs text-gray-500">Couleur des étoiles</span>
                <div className="flex items-center gap-2">
                  <input
                    type="color"
                    value={form.star_color}
                    onChange={e => set('star_color')(e.target.value)}
                    className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5 rounded"
                  />
                  <code className="text-xs text-gray-400">{form.star_color}</code>
                </div>
              </label>
              <Input
                label="Label badge certifié"
                value={form.certified_label}
                onChange={e => set('certified_label')(e.target.value)}
                placeholder="Certifié"
              />
            </div>
          </section>

          {/* ── Liaison avis → post ──────────────────────────── */}
          <section>
            <SectionHeader>Liaison avis ↔ Post</SectionHeader>
            <p className="text-xs text-gray-500 mb-3">
              Sélectionnez les types de contenu auxquels un avis peut être lié.
              Une meta box <strong>« Lieu SJ Reviews »</strong> apparaîtra également sur ces contenus pour lier chaque page à un lieu.
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
                      className={`flex items-center gap-3 cursor-pointer px-3 py-2 rounded-lg border transition-all duration-150
                        ${checked ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200'}`}
                    >
                      <div className={`w-4 h-4 rounded border-2 flex items-center justify-center transition-all
                        ${checked ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white'}`}>
                        {checked && <IconCheck size={10} strokeWidth={3} className="text-white" />}
                      </div>
                      <input
                        type="checkbox"
                        checked={checked}
                        onChange={() => {
                          const next = checked
                            ? form.linked_post_types.filter(s => s !== pt.slug)
                            : [...form.linked_post_types, pt.slug]
                          setForm(f => ({ ...f, linked_post_types: next }))
                        }}
                        className="sr-only"
                      />
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

          {/* ── Shortcodes ───────────────────────────────────── */}
          <section>
            <SectionHeader>Shortcodes disponibles</SectionHeader>
            <div className="bg-gray-50 border border-gray-200 px-4 py-3 text-xs font-mono text-gray-600 space-y-1 rounded-lg">
              <div className="text-gray-400 mb-2">{/* Avis (liste/slider) */}</div>
              <div>[sj_reviews layout="slider-i" preset="minimal" max="5"]</div>
              <div>[sj_reviews layout="grid" preset="white" columns="3"]</div>
              <div>[sj_reviews lieu_id="lieu_xxxxxxxx"]</div>
              <div>[sj_reviews source="google"]</div>
              <div className="text-gray-400 mt-3 mb-1">{/* Résumé statistique */}</div>
              <div>[sj_summary]</div>
              <div>[sj_summary lieu_id="lieu_xxxxxxxx" show_distribution="1" show_criteria="1"]</div>
            </div>

            <Tutorial title="Comment utiliser le widget Résumé Avis ?">
              <p>Le widget <strong>Résumé Avis</strong> (Elementor → catégorie SJ Reviews) affiche :</p>
              <ul className="ml-3 list-disc space-y-0.5">
                <li>La note globale et son label (Excellent, Très bien…)</li>
                <li>La répartition par étoiles (barres de progression)</li>
                <li>Les sous-critères : Qualité/prix, Ambiance, Expérience, Paysage</li>
              </ul>
              <p>En mode <strong>Auto</strong>, le widget détecte le lieu de la page courante via la meta box <em>Lieu SJ Reviews</em>.</p>
              <p>Retrouvez l'ID de chaque lieu dans <strong>Lieux &amp; Sources</strong>.</p>
            </Tutorial>
          </section>

        </div>
      </form>
    </div>
  )
}
