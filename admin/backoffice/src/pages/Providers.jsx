import { useState, useEffect, useCallback } from 'react'
import { api } from '../lib/api'
import { flushProvidersCache } from '../lib/providers'
import {
  PageHeader, Btn, Input, Select, Toggle,
  Spinner, Badge,
} from '../components/ui'
import { IconPencil, IconTrash, IconPlus, IconCheck } from '../components/Icons'
import { useToast } from '../components/Toast'

const ICON_TYPES = [
  { value: 'svg_inline', label: 'SVG inline' },
  { value: 'img_url',    label: 'URL image' },
  { value: 'emoji',      label: 'Emoji' },
  { value: 'letter',     label: 'Lettre initiale' },
]

const EMPTY_FORM = {
  id: '',
  label: '',
  color: '#9CA3AF',
  icon_type: 'letter',
  icon_value: '',
  icon_url: '',
  external_link_pattern: '',
  active: true,
}

function ProviderIcon({ p, size = 20 }) {
  const { icon_type, icon_value, icon_url, color, label } = p
  if (icon_type === 'svg_inline' && icon_value) {
    return (
      <span
        className="inline-flex items-center justify-center shrink-0"
        style={{ width: size, height: size }}
        aria-label={label}
        dangerouslySetInnerHTML={{ __html: icon_value }}
      />
    )
  }
  if (icon_type === 'img_url' && (icon_url || icon_value)) {
    return (
      <img
        src={icon_url || icon_value}
        alt={label}
        width={size}
        height={size}
        className="rounded object-contain shrink-0"
      />
    )
  }
  if (icon_type === 'emoji' && icon_value) {
    return <span style={{ fontSize: size, lineHeight: 1 }}>{icon_value}</span>
  }
  const letter = icon_value || label?.charAt(0)?.toUpperCase() || '?'
  const fontSize = Math.max(8, Math.round(size * 0.55))
  return (
    <span
      className="inline-flex items-center justify-center rounded-full text-white font-semibold shrink-0"
      style={{ width: size, height: size, background: color || '#9CA3AF', fontSize }}
      aria-hidden="true"
    >
      {letter}
    </span>
  )
}

function ProviderForm({ initial = EMPTY_FORM, onSave, onCancel, saving, isNew = true }) {
  const [form, setForm] = useState({ ...EMPTY_FORM, ...initial })
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  // Auto-generate id from label on new providers
  const handleLabelChange = (e) => {
    const val = e.target.value
    set('label', val)
    if (isNew) {
      set('id', val.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, ''))
    }
  }

  return (
    <form
      onSubmit={e => { e.preventDefault(); onSave(form) }}
      className="grid grid-cols-1 gap-4 sm:grid-cols-2"
    >
      <Input
        label="Nom affiché *"
        value={form.label}
        onChange={handleLabelChange}
        required
        placeholder="Booking.com"
      />
      <Input
        label="Identifiant (slug) *"
        value={form.id}
        onChange={e => set('id', e.target.value)}
        required
        disabled={!isNew}
        placeholder="booking_com"
        className="font-mono text-xs"
      />

      {/* Couleur + aperçu */}
      <div className="flex items-end gap-3">
        <div className="flex-1">
          <Input
            label="Couleur hex"
            value={form.color}
            onChange={e => set('color', e.target.value)}
            placeholder="#4285F4"
            className="font-mono text-xs"
          />
        </div>
        <div
          className="w-8 h-8 rounded border border-border shrink-0"
          style={{ background: form.color || '#9CA3AF' }}
        />
        <ProviderIcon p={form} size={28} />
      </div>

      <Select
        label="Type d'icône"
        value={form.icon_type}
        onChange={e => set('icon_type', e.target.value)}
      >
        {ICON_TYPES.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
      </Select>

      {form.icon_type === 'svg_inline' && (
        <div className="sm:col-span-2">
          <label className="block text-xs font-medium text-muted-foreground mb-1">SVG inline</label>
          <textarea
            className="w-full text-xs font-mono border border-border rounded-md px-3 py-2 bg-background focus:outline-none focus:ring-1 focus:ring-ring"
            rows={4}
            value={form.icon_value}
            onChange={e => set('icon_value', e.target.value)}
            placeholder="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 24 24&quot;>...</svg>"
          />
        </div>
      )}

      {form.icon_type === 'img_url' && (
        <Input
          label="URL de l'image"
          value={form.icon_url}
          onChange={e => set('icon_url', e.target.value)}
          placeholder="https://example.com/logo.png"
          className="sm:col-span-2"
        />
      )}

      {(form.icon_type === 'emoji' || form.icon_type === 'letter') && (
        <Input
          label={form.icon_type === 'emoji' ? 'Emoji' : 'Lettre'}
          value={form.icon_value}
          onChange={e => set('icon_value', e.target.value)}
          placeholder={form.icon_type === 'emoji' ? '🏨' : 'B'}
          maxLength={4}
        />
      )}

      <Input
        label="Pattern lien externe"
        value={form.external_link_pattern}
        onChange={e => set('external_link_pattern', e.target.value)}
        placeholder="https://example.com/reviews?id={place_id}"
        className="sm:col-span-2"
      />
      <div className="sm:col-span-2">
        <p className="text-xs text-muted-foreground">
          Variables disponibles : <code>{'{place_id}'}</code>, <code>{'{domain}'}</code>, <code>{'{location_id}'}</code>
        </p>
      </div>

      <div className="sm:col-span-2 flex items-center justify-between gap-4 py-2 border-t border-border pt-4">
        <div>
          <div className="text-sm font-medium">Provider actif</div>
          <div className="text-xs text-muted-foreground">Visible dans les filtres et sélecteurs</div>
        </div>
        <Toggle checked={form.active} onChange={v => set('active', v)} id="provider-active" />
      </div>

      <div className="flex gap-2 sm:col-span-2">
        <Btn type="submit" disabled={saving}>{saving ? 'Enregistrement…' : 'Enregistrer'}</Btn>
        <Btn variant="ghost" type="button" onClick={onCancel}>Annuler</Btn>
      </div>
    </form>
  )
}

export default function Providers() {
  const toast = useToast()
  const [providers, setProviders] = useState([])
  const [loading, setLoading]     = useState(true)
  const [creating, setCreating]   = useState(false)
  const [editingId, setEditingId] = useState(null)
  const [saving, setSaving]       = useState(false)

  const load = useCallback(async () => {
    try {
      setLoading(true)
      const list = await api.providers()
      setProviders(list)
    } catch (e) {
      toast.error(e.message)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { load() }, [load])

  const handleCreate = async (form) => {
    setSaving(true)
    try {
      const p = await api.createProvider(form)
      setProviders(prev => [...prev, p])
      setCreating(false)
      flushProvidersCache()
      toast.success('Provider créé.')
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  const handleUpdate = async (id, form) => {
    setSaving(true)
    try {
      const p = await api.updateProvider(id, form)
      setProviders(prev => prev.map(x => x.id === id ? p : x))
      setEditingId(null)
      flushProvidersCache()
      toast.success('Provider mis à jour.')
    } catch (e) {
      toast.error(e.message)
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id, label) => {
    if (!window.confirm(`Supprimer le provider « ${label} » ?`)) return
    try {
      await api.deleteProvider(id)
      setProviders(prev => prev.filter(x => x.id !== id))
      flushProvidersCache()
      toast.success('Provider supprimé.')
    } catch (e) {
      toast.error(e.message)
    }
  }

  const handleToggleActive = async (p) => {
    try {
      const updated = await api.updateProvider(p.id, { ...p, active: !p.active })
      setProviders(prev => prev.map(x => x.id === p.id ? updated : x))
      flushProvidersCache()
    } catch (e) {
      toast.error(e.message)
    }
  }

  return (
    <div>
      <PageHeader
        title="Providers"
        actions={
          <Btn size="sm" onClick={() => { setCreating(true); setEditingId(null) }}>
            <IconPlus width={13} height={13} />
            Ajouter un provider
          </Btn>
        }
      />

      {loading ? (
        <div className="flex items-center justify-center py-20"><Spinner size={20} /></div>
      ) : (
        <div className="mx-6 mt-4 space-y-3">

          {/* Formulaire de création */}
          {creating && (
            <div className="rounded-lg border bg-card p-5">
              <p className="text-sm font-medium mb-4">Nouveau provider</p>
              <ProviderForm
                isNew={true}
                onSave={handleCreate}
                onCancel={() => setCreating(false)}
                saving={saving}
              />
            </div>
          )}

          {/* Liste des providers */}
          <div className="rounded-lg border divide-y">
            {providers.length === 0 && (
              <p className="text-sm text-muted-foreground p-5 italic">Aucun provider configuré.</p>
            )}
            {providers.map(p => (
              <div key={p.id}>
                {editingId === p.id ? (
                  <div className="p-5">
                    <ProviderForm
                      initial={p}
                      isNew={false}
                      onSave={form => handleUpdate(p.id, form)}
                      onCancel={() => setEditingId(null)}
                      saving={saving}
                    />
                  </div>
                ) : (
                  <div className="flex items-center gap-4 px-4 py-3">
                    {/* Icône */}
                    <ProviderIcon p={p} size={20} />

                    {/* Nom + id */}
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <span className="text-sm font-medium">{p.label}</span>
                        {p.is_system && (
                          <span className="text-xs text-muted-foreground border border-border rounded px-1.5 py-0.5">
                            Système
                          </span>
                        )}
                      </div>
                      <span className="text-xs text-muted-foreground font-mono">{p.id}</span>
                    </div>

                    {/* Couleur */}
                    <div
                      className="w-4 h-4 rounded-full border border-border shrink-0"
                      style={{ background: p.color }}
                      title={p.color}
                    />

                    {/* Toggle actif */}
                    <Toggle
                      checked={p.active}
                      onChange={() => handleToggleActive(p)}
                      id={`provider-active-${p.id}`}
                    />

                    {/* Actions */}
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => { setEditingId(p.id); setCreating(false) }}
                        className="p-1.5 rounded hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                        title="Modifier"
                      >
                        <IconPencil size={14} strokeWidth={1.5} />
                      </button>
                      {!p.is_system && (
                        <button
                          onClick={() => handleDelete(p.id, p.label)}
                          className="p-1.5 rounded hover:bg-destructive/10 transition-colors text-muted-foreground hover:text-destructive"
                          title="Supprimer"
                        >
                          <IconTrash size={14} strokeWidth={1.5} />
                        </button>
                      )}
                    </div>
                  </div>
                )}
              </div>
            ))}
          </div>

          <p className="text-xs text-muted-foreground">
            Les providers <strong>Système</strong> sont intégrés et ne peuvent pas être supprimés.
            Vous pouvez modifier leur libellé, couleur et icône.
          </p>
        </div>
      )}
    </div>
  )
}
