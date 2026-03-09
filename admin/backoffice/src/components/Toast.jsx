import { useState, useEffect, useCallback, useMemo, createContext, useContext } from 'react'

const ToastContext = createContext(null)

export function useToast() {
  return useContext(ToastContext)
}

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const addToast = useCallback((message, type = 'success', duration = 4000) => {
    const id = Date.now() + Math.random()
    setToasts(t => [...t, { id, message, type }])
    if (duration > 0) {
      setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), duration)
    }
  }, [])

  const dismiss = useCallback((id) => {
    setToasts(t => t.filter(x => x.id !== id))
  }, [])

  const api = useMemo(() => {
    const fn = (msg, type, dur) => addToast(msg, type, dur)
    fn.success = (msg, dur) => addToast(msg, 'success', dur)
    fn.error = (msg, dur) => addToast(msg, 'error', dur ?? 6000)
    fn.info = (msg, dur) => addToast(msg, 'info', dur)
    fn.warn = (msg, dur) => addToast(msg, 'warn', dur)
    return fn
  }, [addToast])

  return (
    <ToastContext.Provider value={api}>
      {children}
      <ToastContainer toasts={toasts} onDismiss={dismiss} />
    </ToastContext.Provider>
  )
}

function ToastContainer({ toasts, onDismiss }) {
  if (!toasts.length) return null

  return (
    <div
      className="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none"
      aria-live="polite"
      aria-relevant="additions removals"
    >
      {toasts.map(t => (
        <ToastItem key={t.id} toast={t} onDismiss={() => onDismiss(t.id)} />
      ))}
    </div>
  )
}

function ToastItem({ toast, onDismiss }) {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    requestAnimationFrame(() => setVisible(true))
  }, [])

  const styles = {
    success: 'bg-black text-white',
    error:   'bg-red-600 text-white',
    info:    'bg-gray-700 text-white',
    warn:    'bg-amber-500 text-black',
  }

  const icons = {
    success: (
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    ),
    error: (
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
        <circle cx="12" cy="12" r="10" />
        <line x1="15" y1="9" x2="9" y2="15" />
        <line x1="9" y1="9" x2="15" y2="15" />
      </svg>
    ),
    info: (
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 16v-4M12 8h.01" />
      </svg>
    ),
    warn: (
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
    ),
  }

  return (
    <div
      className={`pointer-events-auto flex items-center gap-2.5 px-4 py-3 text-sm font-medium shadow-lg
        transition-all duration-300 max-w-sm cursor-pointer
        ${styles[toast.type] ?? styles.info}
        ${visible ? 'translate-y-0 opacity-100' : 'translate-y-3 opacity-0'}`}
      onClick={onDismiss}
      role="alert"
    >
      <span className="shrink-0">{icons[toast.type] ?? icons.info}</span>
      <span className="flex-1">{toast.message}</span>
      <button
        onClick={e => { e.stopPropagation(); onDismiss() }}
        className="shrink-0 opacity-60 hover:opacity-100 ml-1"
        aria-label="Fermer"
      >
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>
  )
}
