import { StrictMode } from 'react'
import { createRoot }  from 'react-dom/client'
import { HashRouter }  from 'react-router-dom'
import App             from './App'
import './index.css'

const el = document.getElementById('sj-reviews-root')
if (el) {
  // Si pas de hash, démarre sur /dashboard
  if (!window.location.hash || window.location.hash === '#/') {
    window.location.replace(window.location.pathname + window.location.search + '#/dashboard')
  }

  createRoot(el).render(
    <StrictMode>
      <HashRouter>
        <App />
      </HashRouter>
    </StrictMode>
  )
}
