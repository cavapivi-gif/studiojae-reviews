import { StrictMode } from 'react'
import { createRoot }  from 'react-dom/client'
import { HashRouter }  from 'react-router-dom'
import App             from './App'
import './index.css'

const el = document.getElementById('sj-reviews-root')
if (el) {
  // Lit la route initiale injectée par PHP (data-route="/dashboard" ou "/settings")
  // et initialise le hash si la page vient d'être chargée sans hash
  const targetRoute = el.dataset.route ?? '/dashboard'
  if (!window.location.hash || window.location.hash === '#/') {
    window.location.replace(window.location.pathname + window.location.search + '#' + targetRoute)
  }

  createRoot(el).render(
    <StrictMode>
      <HashRouter>
        <App />
      </HashRouter>
    </StrictMode>
  )
}
