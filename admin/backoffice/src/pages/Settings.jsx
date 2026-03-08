import { useState, useEffect } from 'react'
import { api } from '../lib/api'
import { PageHeader, Input, Select, Btn, Notice, Spinner } from '../components/ui'
import { Check } from 'iconoir-react'

const DEFAULTS = {
  place_id:        '',
  default_layout:  'slider-i',
  default_preset:  'minimal',
  star_color:      '#f5a623',
  certified_label: 'Certifié',
  max_front:       '5',
}

export default function Settings() {
  const [form, setForm]       = useState(DEFAULTS)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving]   = useState(false)
  const [saved, setSaved]     = useState(false)
  const [error, setError]     = useState(null)

  useEffect(() => {
    api.settings()
      .then(s => setForm({ ...DEFAULTS, ...s }))
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  const set = key => value => setForm(f => ({ ...f, [key]: value }))

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

  if (loading) {
    return (
      <div>
        <PageHeader title="Réglages" />
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      </div>
    )
  }

  return (
    <div>
      <PageHeader
        title="Réglages"
        actions={
          <Btn size="sm" loading={saving} onClick={handleSave}>
            <Check width={13} height={13} />
            Enregistrer
          </Btn>
        }
      />

      <form onSubmit={handleSave} className="px-8 py-6 max-w-xl">
        {error && <div className="mb-4"><Notice type="error">{error}</Notice></div>}
        {saved && <div className="mb-4"><Notice type="success">Réglages enregistrés.</Notice></div>}

        <div className="flex flex-col gap-6">

          <section>
            <div className="text-xs text-gray-400 uppercase tracking-widest mb-3 pb-2 border-b border-gray-100">
              Google My Business
            </div>
            <div className="flex flex-col gap-4">
              <Input
                label="Google Place ID"
                value={form.place_id}
                onChange={e => set('place_id')(e.target.value)}
                placeholder="ChIJ…"
              />
              <p className="text-xs text-gray-400">
                Optionnel. Permet d'afficher le lien vers votre fiche Google dans les widgets badge.
                Sans l'API GMB, seuls les avis CPT sont affichés (max 5 recommandés pour le mode badge).
              </p>
            </div>
          </section>

          <section>
            <div className="text-xs text-gray-400 uppercase tracking-widest mb-3 pb-2 border-b border-gray-100">
              Affichage par défaut
            </div>
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

          <section>
            <div className="text-xs text-gray-400 uppercase tracking-widest mb-3 pb-2 border-b border-gray-100">
              Personnalisation
            </div>
            <div className="grid grid-cols-2 gap-4">
              <label className="flex flex-col gap-1">
                <span className="text-xs text-gray-500">Couleur des étoiles</span>
                <div className="flex items-center gap-2">
                  <input
                    type="color"
                    value={form.star_color}
                    onChange={e => set('star_color')(e.target.value)}
                    className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5"
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

          <section>
            <div className="text-xs text-gray-400 uppercase tracking-widest mb-3 pb-2 border-b border-gray-100">
              Shortcode
            </div>
            <div className="bg-gray-50 border border-gray-200 px-4 py-3 text-xs font-mono text-gray-600 space-y-1">
              <div>[sj_reviews layout="slider-i" preset="minimal" max="5"]</div>
              <div>[sj_reviews layout="badge" preset="dark"]</div>
              <div>[sj_reviews layout="grid" preset="white" columns="3"]</div>
              <div>[sj_reviews place_id="ChIJ..."]</div>
            </div>
          </section>

        </div>
      </form>
    </div>
  )
}
