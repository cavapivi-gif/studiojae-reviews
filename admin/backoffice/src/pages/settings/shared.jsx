import { useState } from 'react'
import { Input, Btn } from '../../components/ui'
import { IconCheck } from '../../components/Icons'

/**
 * Pill de statut (ok / error / neutral) pour les retours de test API.
 * @param {{ status: 'ok'|'error'|undefined, children: React.ReactNode }} props
 */
function Pill({ status, children }) {
  const cls = {
    ok:    'bg-emerald-50 border-emerald-200 text-emerald-700',
    error: 'bg-red-50 border-red-200 text-red-700',
  }[status] ?? 'bg-gray-50 border-gray-200 text-gray-600'
  return <div className={`flex items-start gap-2 text-xs border px-3 py-2 ${cls}`}>{children}</div>
}

/**
 * Bloc tutoriel collapsible avec icône info.
 * @param {{ title: string, children: React.ReactNode }} props
 */
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

/**
 * En-tête de section avec badge optionnel.
 * @param {{ children: React.ReactNode, badge?: string }} props
 */
function SectionHeader({ children, badge }) {
  return (
    <div className="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
      <span className="text-xs font-bold text-gray-500 uppercase tracking-widest">{children}</span>
      {badge && <span className="text-[10px] font-semibold bg-indigo-100 text-indigo-600 px-2 py-0.5">{badge}</span>}
    </div>
  )
}

/**
 * Champ clé API avec bouton de test et retour visuel de statut.
 * @param {{ label, value, onChange, onTest, testStatus, testMsg, badge, tutorial }} props
 */
function ApiKeyField({ label, value, onChange, onTest, testStatus, testMsg, badge, tutorial }) {
  const dot = value?.trim() ? <span className="inline-block w-2 h-2 rounded-full bg-blue-500 ml-1.5 shrink-0" title="Connecté" /> : null
  return (
    <div className="flex flex-col gap-3">
      <div className="flex gap-2 items-end">
        <div className="flex-1">
          <Input label={<>{label}{dot}</>} type="password" value={value} onChange={e => onChange(e.target.value)} placeholder="Clé API…" autoComplete="off" />
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

export { Pill, Tutorial, SectionHeader, ApiKeyField }
