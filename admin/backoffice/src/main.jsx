import { StrictMode } from 'react'
import { createRoot }  from 'react-dom/client'
import { HashRouter }  from 'react-router-dom'
import { ToastProvider } from './components/Toast'
import App             from './App'
import './index.css'

const el = document.getElementById('sj-reviews-root')
if (el) {
  // Si pas de hash, démarre sur /dashboard (sans rechargement de page)
  if (!window.location.hash || window.location.hash === '#/') {
    history.replaceState(null, '', window.location.pathname + window.location.search + '#/dashboard')
  }

  createRoot(el).render(
    <StrictMode>
      <HashRouter>
        <ToastProvider>
          <App />
        </ToastProvider>
      </HashRouter>
    </StrictMode>
  )
}
